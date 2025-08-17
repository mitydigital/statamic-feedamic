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
                'handle' => 'date_time_behaviour',
                'title' => 'Date Time Behaviour Test Test',
                'description' => 'Testing of datetime behaviours',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/datetime/feed/atom',
                    'rss' => '/datetime/feed',
                ],
                'collections' => [
                    'date_time_behaviour_test',
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
    $this->config = Feedamic::getConfig('/datetime/feed/atom', 'default');

    $this->collection = Collection::find('date_time_behaviour_test');
});

it('correctly returns entries in a dated collection based on collection configuration',
    function (string $past, string $future, int $count, array $titles) {
        // reconfigure collection

        $this->collection->futureDateBehavior($future);
        $this->collection->pastDateBehavior($past);
        $this->collection->save();

        $this->travelTo(Carbon::parse('2025-08-14 14:50:00'));
        $this->freezeTime();

        // get the entries
        $entries = Feedamic::getEntries($this->config)->all();

        $entryTitles =
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray();

        expect($entries)->toHaveCount($count)
            ->and($entryTitles)->toBe($titles);
    })->with([
        'public and public' => [
            'public', 'public', 3, ['Later', 'Now', 'Early'],
        ],
        'public and private' => [
            'public', 'private', 2, ['Now', 'Early'],
        ],
        'public and unlisted' => [
            'public', 'unlisted', 2, ['Now', 'Early'],
        ],
        'private and public' => [
            'private', 'public', 2, ['Later', 'Now'],
        ],
        'unlisted and public' => [
            'unlisted', 'public', 2, ['Later', 'Now'],
        ],
    ]);

it('correctly has minute awareness',
    function (string $time, int $count, array $titles) {
        // reconfigure collection

        $this->collection->futureDateBehavior('unlisted');
        $this->collection->pastDateBehavior('public');
        $this->collection->save();

        $this->travelTo(Carbon::parse($time));
        $this->freezeTime();

        // get the entries
        $entries = Feedamic::getEntries($this->config)->all();

        $entryTitles =
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray();

        expect($entries)->toHaveCount($count)
            ->and($entryTitles)->toBe($titles);
    })->with([
        'none' => [
            '2025-08-14 00:00:00', 0, [],
        ],
        'early' => [
            '2025-08-14 00:01:00', 1, ['Early'],
        ],
        'early now before' => [
            '2025-08-14 14:49:59', 1, ['Early'],
        ],
        'early now' => [
            '2025-08-14 14:50:00', 2, ['Now', 'Early'],
        ],
        'early now later before' => [
            '2025-08-14 22:59:59', 2, ['Now', 'Early'],
        ],
        'early now later' => [
            '2025-08-14 23:00:00', 3, ['Later', 'Now', 'Early'],
        ],
    ]);
