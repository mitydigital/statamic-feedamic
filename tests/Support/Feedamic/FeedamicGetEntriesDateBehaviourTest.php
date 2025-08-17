<?php

use Carbon\Carbon;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\Collection;
use Statamic\Facades\YAML;

beforeEach(function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'date_behaviour',
                'title' => 'Date Behaviour Test Test',
                'description' => 'Testing of date behaviours',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/date/feed/atom',
                    'rss' => '/date/feed',
                ],
                'collections' => [
                    'date_behaviour_test',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'default',
                    'author_mode' => 'default',
                    'content_mode' => 'default',
                ],
            ],
        ],
        'default_summary' => [],
    ]));
    Feedamic::load(true);

    // get the config
    $this->config = Feedamic::getConfig('/date/feed/atom', 'default');

    $this->collection = Collection::find('date_behaviour_test');
});

it('correctly returns entries in a dated collection based on collection configuration',
    function (string $past, string $future, int $count, array $titles) {
        // reconfigure collection
        $this->collection->futureDateBehavior($future);
        $this->collection->pastDateBehavior($past);
        $this->collection->save();

        $this->travelTo(Carbon::parse('2025-08-14'));
        $this->freezeTime();

        // get the entries
        $entries = Feedamic::getEntries($this->config)->all();

        $entryTitles = collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray();

        expect($entries)->toHaveCount($count)
            ->and($entryTitles)->toBe($titles);
    })->with([
        'public and public' => [
            'public', 'public', 3, ['Later', 'Today', 'Early'],
        ],
        'public and private' => [
            'public', 'private', 2, ['Today', 'Early'],
        ],
        'public and unlisted' => [
            'public', 'unlisted', 2, ['Today', 'Early'],
        ],
        'private and public' => [
            'private', 'public', 2, ['Later', 'Today'],
        ],
        'unlisted and public' => [
            'unlisted', 'public', 2, ['Later', 'Today'],
        ],
    ]);
