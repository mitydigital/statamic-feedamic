<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MityDigital\Feedamic\Feedamic;
use MityDigital\Feedamic\Models\FeedEntry;
use MityDigital\Feedamic\Models\FeedEntryAuthor;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
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
        if (Feedamic::version() >= '2.2.0' && $feed) {
            // v2.2 or above
            $cacheXml = config('feedamic.cache').'.'.$feed.'.'.$type;
            $cacheEntries = config('feedamic.cache').'.'.$feed;
        } else {
            // v2.1 or below
            $cacheXml = config('feedamic.cache').'.'.$type;
            $cacheEntries = config('feedamic.cache');
        }

        // build the xml
        $xml = Cache::rememberForever($cacheXml, function () use ($cacheEntries, $feed, $type) {
            // get the entries
            $entries = Cache::rememberForever($cacheEntries, fn() => $this->loadFeedEntries($feed));

            // get the base url for the feed - this is the <id>, and must be a canonical representation
            $uri = config('app.url');
            if (substr($uri, -1) != '/') {
                $uri .= '/';
            }

            // get params, fallbacks or defaults
            $params = [
                'title' => $this->getConfigValue($feed, 'title'),
                'description' => $this->getConfigValue($feed, 'description'),
                'language' => $this->getConfigValue($feed, 'language'),
                'alt_url' => URL::makeAbsolute($this->getConfigValue($feed, 'alt_url', config('app.url'), true)),
                'href' => URL::makeAbsolute($this->getConfigValue($feed, 'routes.atom')),
                'copyright' => $this->getConfigValue($feed, 'copyright'),
                'author_email' => $this->getConfigValue($feed, 'author.email')
            ];

            // return the Atom view
            $xml = view('mitydigital/feedamic::'.$type, array_merge([
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
                return ''.$tidy;
            }

            // otherwise just return some plain jane xml
            return $xml;
        });

        // add the XML header
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$xml;

        return response($xml, 200, ['Content-Type' => 'application/'.$type.'+xml; charset=UTF-8']);
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
        if (Feedamic::version() >= '2.2.0' && $feed) {
            // v2.2 or above
            $collections = config('feedamic.feeds.'.$feed.'.collections');
        } else {
            // v2.1 or below
            $collections = config('feedamic.collections');
        }

        // get the taxonomies for later
        $taxonomies = config('feedamic.feeds.'.$feed.'.taxonomies', []);

        // filter entries by their locales; include all locales by default
        $locales = $this->getConfigValue($feed, 'locales', '*', true);
        // dynamically get current site handle for special locales value 'current'
        $locales = $locales === 'current' ? [Site::current()->handle()] : $locales;

        $entries = collect($collections)->flatMap(function ($handle) use ($feed, $locales, $taxonomies) {
            $collection = Collection::findByHandle($handle);

            // load the entries for this collection
            $queryBuilder = $collection
                ->queryEntries();

            // filter by taxonomy terms
            foreach ($taxonomies as $taxonomy => $termsConfig) {
                $logic = strtolower(Arr::get($termsConfig, 'logic', 'and'));
                switch($logic)
                {
                    case 'and':
                        foreach (Arr::get($termsConfig, 'handles', []) as $term) {
                            $queryBuilder = $queryBuilder->whereTaxonomy($taxonomy.'::'.$term);
                        }
                        break;
                    case 'or':
                        // OR LOGIC
                        $taxonomyTerms = [];
                        foreach (Arr::get($termsConfig, 'handles', []) as $term) {
                            $taxonomyTerms[] = $taxonomy.'::'.$term;
                        }
                        $queryBuilder = $queryBuilder->whereTaxonomyIn($taxonomyTerms);
                        break;
                }
            }

            $entries = $queryBuilder
                ->when($locales !== '*', function ($query) use ($locales) {
                    // only apply locale filter if its value is not a wildcard
                    return $query->whereIn('locale', $locales);
                })
                ->where('published', true)
                ->where(function ($query) use ($collection) {
                    if ($collection->futureDateBehavior() === 'private') {
                        $query->where($collection->sortField(), '<=', now());
                    }

                    if ($collection->pastDateBehavior() === 'private') {
                        $query->where($collection->sortField(), '>=', now());
                    }

                    return $query;
                })
                ->orderBy($collection->sortField(), 'desc')
                ->limit($this->getConfigValue($feed, 'limit', null, true))
                ->get()
                ->map(function (Entry $entry) use ($feed) {
                    // get summary fields
                    $summaryFields = $this->getConfigValue($feed, 'summary', [], true);
                    if (is_string($summaryFields)) {
                        $summaryFields = [$summaryFields];
                    } elseif (is_bool($summaryFields)) {
                        $summaryFields = []; // always want it as an array
                    }

                    // get image fields
                    $imageFields = $this->getConfigValue($feed, 'image.fields', [], true);
                    if (is_string($imageFields)) {
                        $imageFields = [$imageFields];
                    } elseif (is_bool($imageFields) || is_null($imageFields)) {
                        $imageFields = []; // always want it as an array
                    }

                    // get author field
                    $authorField = $this->getConfigValue($feed, 'author.handle', false);

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
                            if ($image && get_class($image) == \Statamic\Assets\OrderedQueryBuilder::class) {
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
                                if (method_exists($entryArray[$authorField], 'value')
                                    && $entryArray[$authorField]->value()
                                    && get_class($entryArray[$authorField]->value()) == 'Statamic\Query\OrderedQueryBuilder') {
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
                        'updated' => $entry->lastModified()
                    ]);
                });

            return $entries;
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
     * Some helper logic to get the config (or a fallback) from the Feedamic config array
     *
     * @param  string|null  $feed
     * @param  string  $key
     * @param  mixed|null  $default
     * @param  bool|null  $useDefaultIfEmpty
     * @return \Illuminate\Config\Repository|Application|mixed
     */
    protected function getConfigValue(
        string|null $feed,
        string $key,
        mixed $default = null,
        bool $useDefaultIfEmpty = null
    ) {
        // set the location
        $location = '';

        // do we have a feed, and does the "feeds" config exist?
        if (Feedamic::version() >= '2.2.0' && $feed && config('feedamic.feeds', false)) {
            // if so, does the key exist in there?
            if (config()->has('feedamic.feeds.'.$feed.'.'.$key)) {
                $location = 'feedamic.feeds.'.$feed.'.'.$key;
            }
        }

        if (!$location) {
            // no 'feeds', so look for the core value
            $location = 'feedamic.'.$key;
        }

        $value = config($location, $default);

        if ($useDefaultIfEmpty && $default && !$value) {
            return $default;
        }

        return $value;
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
