<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use MityDigital\Feedamic\Models\FeedEntry;
use MityDigital\Feedamic\Models\FeedEntryAuthor;
use Statamic\Facades\Collection;

class FeedamicController extends Controller
{
    /**
     * Gets the cached rss feed (or renders if it needs to)
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function rss()
    {
        $xml = Cache::rememberForever(config('statamic.feedamic.cache').'.rss', function () {
            // get the entries
            $entries = Cache::rememberForever(config('statamic.feedamic.cache'), function () {
                return $this->loadFeedEntries();
            });

            // render the RSS view
            return view('mitydigital/feedamic::rss', $entries)->render();
        });

        // add the XML header
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    /**
     * Gets the cached atom feed (or renders if it needs to)
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function atom()
    {
        $xml = Cache::rememberForever(config('statamic.feedamic.cache').'.atom', function () {
            // get the entries
            $entries = Cache::rememberForever(config('statamic.feedamic.cache'), function () {
                return $this->loadFeedEntries();
            });

            // get the base url for the feed - this is the <id>, and must be a canonical representation
            $uri = config('app.url');
            if (substr($uri, -1) != '/') {
                $uri .= '/';
            }

            // return the Atom view
            return view('mitydigital/feedamic::atom', array_merge([
                'id' => $uri
            ], $entries))->render();
        });

        // add the XML header
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;

        return response($xml, 200, ['Content-Type' => 'application/atom+xml; charset=UTF-8']);
    }

    /**
     * Loads the entries from the configured collections.
     *
     * Sorted by published date, descending
     *
     * @return array
     */
    protected function loadFeedEntries()
    {
        $entries = collect(config('statamic.feedamic.collections'))->flatMap(function ($handle) {
            // load the entries for this collection
            return Collection::findByHandle($handle)
                ->queryEntries()
                ->orderBy('updated_at', 'desc')
                ->get()
                ->filter(function (\Statamic\Entries\Entry $entry) {
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
                ->map(function (\Statamic\Entries\Entry $entry) {
                    // get summary fields
                    $summaryFields = config('statamic.feedamic.summary');
                    if (is_string($summaryFields)) {
                        $summaryFields = [$summaryFields];
                    } elseif (is_bool($summaryFields)) {
                        $summaryFields = []; // always want it as an array
                    }

                    // get image fields
                    $imageFields = config('statamic.feedamic.image.fields');
                    if (is_string($imageFields)) {
                        $imageFields = [$imageFields];
                    } elseif (is_bool($imageFields) || is_null($imageFields)) {
                        $imageFields = []; // always want it as an array
                    }

                    // get author field
                    $authorField = config('statamic.feedamic.author.handle');

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
                            $author = new FeedEntryAuthor($entryArray[$authorField]->value());
                        }
                    }

                    // create a feed entry object
                    return new FeedEntry([
                        'title'     => $entryArray['title']->value(),
                        'author'    => $author,
                        'uri'       => config('app.url').$entry->uri(),
                        'summary'   => $summary,
                        'image'     => $image,
                        'published' => $entry->date(),
                        'updated'   => $entry->fileLastModified()
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
}
