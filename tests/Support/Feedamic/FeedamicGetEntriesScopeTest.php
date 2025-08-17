<?php

use App\Scopes\MyQueryScope;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\YAML;

it('does not call the scope when it is not configured', function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'scope',
                'title' => 'Scope Test',
                'description' => 'Testing of scope',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/scope/feed/atom',
                    'rss' => '/scope/feed',
                ],
                'collections' => [
                    'scope_test',
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
    $config = Feedamic::getConfig('/scope/feed/atom', 'default');

    $entries = Feedamic::getEntries($config)->all();

    expect($entries)->toHaveCount(2);

    $entryTitles =
        collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray();

    expect($entryTitles)->toBe([
        'Banana',
        'Apple',
    ]);
});

it('calls the scope when it configured', function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'scope',
                'title' => 'Scope Test',
                'description' => 'Testing of scope',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/scope/feed/atom',
                    'rss' => '/scope/feed',
                ],
                'collections' => [
                    'scope_test',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'default',
                    'author_mode' => 'default',
                    'content_mode' => 'default',
                ],
                'scope' => MyQueryScope::class,
            ],
        ],
        'default_summary' => [],
    ]));
    Feedamic::load(true);

    // get the config
    $config = Feedamic::getConfig('/scope/feed/atom', 'default');

    $entries = Feedamic::getEntries($config)->all();

    expect($entries)->toHaveCount(1);

    $entryTitles =
        collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray();

    expect($entryTitles)->toBe([
        'Banana',
    ]);
});
