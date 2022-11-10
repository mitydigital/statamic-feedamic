<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedEntry;
use MityDigital\Feedamic\Models\FeedEntryAuthor;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\URL;

class FeedamicController extends Controller
{
    /**
     * Gets the cached rss feed (or renders if it needs to)
     *
     * @return Application|ResponseFactory|Response
     */
    public function rss(string $feed = null)
    {
        return $this->getXml($feed, 'rss');
    }

    /**
     * Gets the XML response for a given feed and type
     *
     * @param $feed
     * @param $type
     * @return Application|ResponseFactory|Response
     */
    protected function getXml($feed, $type)
    {
        $cacheXml = Feedamic::getCacheKey($feed, $type);
        $cacheEntries = Feedamic::getCacheKey($feed);

        // build the xml
        $xml = Cache::rememberForever($cacheXml, function () use ($cacheEntries, $feed, $type) {
            // get the entries
            $entries = Cache::rememberForever($cacheEntries, fn () => $this->loadFeedEntries($feed));

            // get the base url for the feed - this is the <id>, and must be a canonical representation
            $uri = config('app.url');
            if (substr($uri, -1) != '/') {
                $uri .= '/';
            }

            // get params, fallbacks or defaults
            $params = [
                'title' => Feedamic::getConfigValue($feed, 'title'),
                'description' => Feedamic::getConfigValue($feed, 'description'),
                'language' => Feedamic::getConfigValue($feed, 'language'),
                'alt_url' => URL::makeAbsolute(Feedamic::getConfigValue($feed, 'alt_url', config('app.url'), true)),
                'href' => URL::makeAbsolute(Feedamic::getConfigValue($feed, 'routes.atom')),
                'copyright' => Feedamic::getConfigValue($feed, 'copyright'),
                'author_email' => Feedamic::getConfigValue($feed, 'author.email')
            ];

            // return the Atom view
            $xml = view('mitydigital/feedamic::' . $type, array_merge([
                'id' => $uri
            ], array_merge($params, $entries)))->render();

            // if the tidy extension exists, use it
            if (extension_loaded('tidy')) {
                $tidy = new \tidy;
                $tidy->parseString($xml, [
                    'indent' => true,
                    'output-xml' => true,
                    'input-xml' => true,
                    'wrap' => 0
                ], 'utf8');
                $tidy->cleanRepair();

                // return tidy as a string
                return '' . $tidy;
            }

            // otherwise just return some plain jane xml
            return $xml;
        });

        // add the XML header
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

        return response($xml, 200, ['Content-Type' => 'application/' . $type . '+xml; charset=UTF-8']);
    }

    /**
     * Loads the entries from the configured collections.
     *
     * Sorted by published date, descending
     *
     * @return array
     */
    protected function loadFeedEntries(string $feed = null)
    {
        $collections = Feedamic::getCollections($feed);

        // get configured locales for multi-site support
        $locales = Feedamic::getLocales($feed);

        $entries = collect($collections)->flatMap(function ($handle) use ($feed, $locales) {
            // load the entries for this collection
            return Collection::findByHandle($handle)
                ->queryEntries()
                ->when($locales !== '*', function ($query) use ($locales) {
                    // only apply locale filter if its value is not a wildcard
                    return $query->whereIn('locale', $locales);
                })
                ->orderBy('published_at', 'desc')
                ->get()
                ->filter(function (Entry $entry) {
                    // is the entry published?
                    if (!$entry->published()) {
                        return false;
                    }

                    // if future listings are private, do not include
                    if ($entry->collection()->futureDateBehavior() == 'private') {
                        if ($entry->date() > now()) {
                            return false;
                        }
                    }

                    // if past listings are private, do not include
                    if ($entry->collection()->pastDateBehavior() == 'private') {
                        if ($entry->date() < now()) {
                            return false;
                        }
                    }


                    // this far, we keep it
                    return true;
                })
                ->limit(Feedamic::getConfigValue($feed, 'limit', null, true))
                ->map(function (Entry $entry) use ($feed) {
                    // get summary fields
                    $summaryFields = Feedamic::getConfigValue($feed, 'summary', [], true);
                    if (is_string($summaryFields)) {
                        $summaryFields = [$summaryFields];
                    } elseif (is_bool($summaryFields)) {
                        $summaryFields = []; // always want it as an array
                    }

                    // get image fields
                    $imageFields = Feedamic::getConfigValue($feed, 'image.fields', [], true);
                    if (is_string($imageFields)) {
                        $imageFields = [$imageFields];
                    } elseif (is_bool($imageFields) || is_null($imageFields)) {
                        $imageFields = []; // always want it as an array
                    }

                    // get author field
                    $authorField = Feedamic::getConfigValue($feed, 'author.handle', false);

                    // set up as if it were the basic sort
                    /*$authorField = $authorConfig;
                    $authorType = 'basic';

                    // if we have config
                    if ($authorConfig) {
                        // if it is a string, it's the new type
                        if (is_string($authorConfig)) {
                            $authorType = 'class';
                            $authorField = new $authorConfig($entry);
                        }
                    }*/


                    // add the title to the augmented fields
                    $summaryFields[] = 'title';

                    // only augment the required fields
                    $augmentedFields = array_merge($imageFields, $summaryFields);
                    if ($authorField) {
                        $augmentedFields = array_merge([$authorField], $imageFields, $summaryFields);
                    }

                    // augment the entry
                    $entryArray = $entry->toAugmentedArray($augmentedFields);

                    // find the summary - loop through each summary field until we find a value (or we have nothing still)
                    $summary = '';
                    foreach ($summaryFields as $summaryField) {
                        if ($entryArray[$summaryField] && get_class($entryArray[$summaryField]) == 'Statamic\Fields\Value') {
                            // process the value - this will process the Bard fieldtype if it is one
                            $summary = $entryArray[$summaryField]->value();

                            // if we have a value, exit the queue
                            if ($summary) {
                                break;
                            }
                        }
                    }

                    // find the image - just like the summary
                    $image = false;
                    foreach ($imageFields as $imageField) {
                        if ($entryArray[$imageField] && get_class($entryArray[$imageField]) == 'Statamic\Fields\Value') {
                            // process the value
                            $image = $entryArray[$imageField]->value();

                            // if the image asset allows multiple to be selected, just pick the first one
                            if ($image && get_class($image) == \Illuminate\Support\Collection::class) {
                                $image = $image->first();
                            }

                            // if we have a value, exit the queue
                            if ($image) {
                                break;
                            }
                        }
                    }

                    // if we have an authorField, try to find the author
                    $author = false;
                    if ($authorField) {
                        // do we have an author name
                        if ($entryArray[$authorField] && get_class($entryArray[$authorField]) == 'Statamic\Fields\Value') {
                            // if there is a value set
                            if ($entryArray[$authorField]->raw()) {
                                // adds support for user collections (if there are multiple) although the template will only
                                // output the first author
                                if (
                                    method_exists($entryArray[$authorField], 'value')
                                    && $entryArray[$authorField]->value()
                                    && get_class($entryArray[$authorField]->value()) == 'Statamic\Query\OrderedQueryBuilder'
                                ) {
                                    // only include if there is at least one
                                    if ($entryArray[$authorField]->value()->count()) {
                                        $author = new FeedEntryAuthor($entryArray[$authorField]->value()->get(), $feed);
                                    }
                                } else {
                                    if ($entryArray[$authorField]->value()) {
                                        $author = new FeedEntryAuthor($entryArray[$authorField]->value(), $feed);
                                    }
                                }
                            }
                        }
                    }


                    // create a feed entry object
                    return new FeedEntry([
                        'title' => $entryArray['title']->value(),
                        'author' => $author,
                        'uri' => $entry->absoluteUrl(),
                        'summary' => $summary,
                        'image' => $image,
                        'published' => $entry->date(),
                        'updated' => $entry->fileLastModified()
                    ]);
                });

            return $e;
        })->sortBy('published', SORT_REGULAR, true);

        // if we have entries, get the last updated date
        $lastUpdated = false;
        if ($entries->count()) {
            // get last updated time
            $lastUpdated = $entries->reduce(function ($carry, $entry) {
                return max($carry, $entry->updated);
            });
        }

        return [
            'entries' => $entries,
            'updated' => $lastUpdated
        ];
    }

    /**
     * Gets the cached atom feed (or renders if it needs to)
     *
     * @return Application|ResponseFactory|Response
     */
    public function atom(string $feed = null)
    {
        return $this->getXml($feed, 'atom');
    }
}
