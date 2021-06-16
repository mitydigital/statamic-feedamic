<?php

namespace MityDigital\StatamicRssFeed\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use MityDigital\StatamicRssFeed\Models\FeedEntry;
use MityDigital\StatamicRssFeed\Models\FeedEntryAuthor;
use Statamic\Facades\Collection;
use Statamic\View\View;

class StatamicRssFeedController extends Controller
{
    /**
     * Gets the cached rss feed (or renders if it needs to)
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function rss()
    {
        $xml = Cache::rememberForever(config('statamic.rss.cache').'.rss', function () {
            // get the entries
            $entries = Cache::rememberForever(config('statamic.rss.cache'), function () {
                return $this->loadFeedEntries();
            });

            // render the RSS view
            return view('mitydigital/statamic-rss-feed::rss', $entries)->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }

    /**
     * Gets the cached atom feed (or renders if it needs to)
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function atom()
    {
        $xml = Cache::rememberForever(config('statamic.rss.cache').'.atom', function ()  {
            // get the entries
            $entries = Cache::rememberForever(config('statamic.rss.cache'), function () {
                return $this->loadFeedEntries();
            });

            // get the base url for the feed - this is the <id>, and must be a canonical representation
            $uri = config('app.url');
            if (substr($uri, -1) != '/') {
                $uri .= '/';
            }

            // return the Atom view
            return view('mitydigital/statamic-rss-feed::atom', array_merge([
                'id' => $uri
            ], $entries))->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/atom+xml']);
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
        $entries = collect(config('statamic.rss.collections'))->flatMap(function ($handle) {
            // load the entries for this collection
            return Collection::findByHandle($handle)
                ->queryEntries()
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function (\Statamic\Entries\Entry $entry) {
                    // get summary fields
                    $summaryFields = config('statamic.rss.summary');
                    $authorField   = config('statamic.rss.author.handle');

                    // only augment the required fields
                    $augmentedFields = $summaryFields;
                    if ($authorField) {
                        $augmentedFields = array_merge([$authorField], $summaryFields);
                    }

                    // augment the entry
                    $entryArray = $entry->toAugmentedArray($augmentedFields);

                    // find the summary - loop through each summary field until we find a value (or we have nothing still)
                    $summary = '';
                    foreach ($summaryFields as $summaryField) {
                        if ($entryArray[$summaryField] && get_class($entryArray[$summaryField]) == 'Statamic\Fields\Value') {
                            // process the value - this will process the Bard fieldtype if it is one
                            $summary = strip_tags($entryArray[$summaryField]->value());

                            // if we have a value, exit the queue
                            if ($summary) {
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
                        'title'     => $entry->title,
                        'author'    => $author,
                        'uri'       => config('app.url').$entry->uri(),
                        'summary'   => $summary,
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
