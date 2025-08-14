<?php

use Illuminate\Support\Facades\File;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;

beforeEach(function () {
    File::ensureDirectoryExists(base_path('content/collections'));
    File::ensureDirectoryExists(base_path('content/taxonomies'));
    File::copy(
        __DIR__.'/../../__fixtures__/content/collections/taxonomy_test.yaml',
        base_path('content/collections/taxonomy_test.yaml')
    );
    File::copyDirectory(
        __DIR__.'/../../__fixtures__/content/collections/taxonomy_test',
        base_path('content/collections/taxonomy_test')
    );

    File::copy(
        __DIR__.'/../../__fixtures__/content/taxonomies/categories.yaml',
        base_path('content/taxonomies/categories.yaml')
    );
    File::copyDirectory(
        __DIR__.'/../../__fixtures__/content/taxonomies/categories',
        base_path('content/taxonomies/categories')
    );

    File::copyDirectory(
        __DIR__.'/../../__fixtures__/resources/blueprints/collections/taxonomy_test',
        resource_path('blueprints/collections/taxonomy_test')
    );
    File::copyDirectory(
        __DIR__.'/../../__fixtures__/resources/blueprints/taxonomies/categories',
        resource_path('blueprints/taxonomies/categories')
    );

    Stache::warm();
});

it('correctly returns 4 entries with no taxonomies', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'taxonomies',
                'title' => 'Taxonomies Test',
                'description' => 'Testing of taxonomy term filtering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/taxonomies/feed/atom',
                    'rss' => '/taxonomies/feed',
                ],
                'collections' => [
                    'taxonomy_test',
                ],
                'taxonomies' => [],
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
    $config = Feedamic::getConfig('/taxonomies/feed/atom', 'default');

    $entries = Feedamic::getEntries($config);
    expect($entries->all())->toHaveCount(4);
});

it('correctly returns the "apple" entries', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'taxonomies',
                'title' => 'Taxonomies Test',
                'description' => 'Testing of taxonomy term filtering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/taxonomies/feed/atom',
                    'rss' => '/taxonomies/feed',
                ],
                'collections' => [
                    'taxonomy_test',
                ],
                'taxonomies' => [
                    [
                        'terms' => [
                            'categories::apple',
                        ],
                        'logic' => 'and',
                    ],
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
    $config = Feedamic::getConfig('/taxonomies/feed/atom', 'default');

    // expect the entries
    $entries = Feedamic::getEntries($config)->all();
    expect($entries)->toHaveCount(2)
        ->and(
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray()
        )->toBe([
            'Apple Only',
            'Both',
        ]);
});

it('correctly returns the "Banana" entries', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'taxonomies',
                'title' => 'Taxonomies Test',
                'description' => 'Testing of taxonomy term filtering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/taxonomies/feed/atom',
                    'rss' => '/taxonomies/feed',
                ],
                'collections' => [
                    'taxonomy_test',
                ],
                'taxonomies' => [
                    [
                        'terms' => [
                            'categories::banana',
                        ],
                        'logic' => 'and',
                    ],
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
    $config = Feedamic::getConfig('/taxonomies/feed/atom', 'default');

    // expect the entries
    $entries = Feedamic::getEntries($config)->all();
    expect($entries)->toHaveCount(2)
        ->and(
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray()
        )->toBe([
            'Both',
            'Banana Only',
        ]);
});

it('correctly returns ANY "Apple" or "Banana" entries', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'taxonomies',
                'title' => 'Taxonomies Test',
                'description' => 'Testing of taxonomy term filtering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/taxonomies/feed/atom',
                    'rss' => '/taxonomies/feed',
                ],
                'collections' => [
                    'taxonomy_test',
                ],
                'taxonomies' => [
                    [
                        'terms' => [
                            'categories::apple',
                            'categories::banana',
                        ],
                        'logic' => 'or',
                    ],
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
    $config = Feedamic::getConfig('/taxonomies/feed/atom', 'default');

    // expect the entries
    $entries = Feedamic::getEntries($config)->all();
    expect($entries)->toHaveCount(3)
        ->and(
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray()
        )->toBe([
            'Apple Only',
            'Both',
            'Banana Only',
        ]);
});

it('correctly returns ONLY "Apple" AND "Banana" entries', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

    // add the "content" feed
    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'taxonomies',
                'title' => 'Taxonomies Test',
                'description' => 'Testing of taxonomy term filtering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/taxonomies/feed/atom',
                    'rss' => '/taxonomies/feed',
                ],
                'collections' => [
                    'taxonomy_test',
                ],
                'taxonomies' => [
                    [
                        'terms' => [
                            'categories::apple',
                            'categories::banana',
                        ],
                        'logic' => 'and',
                    ],
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
    $config = Feedamic::getConfig('/taxonomies/feed/atom', 'default');

    // expect the entries
    $entries = Feedamic::getEntries($config)->all();
    expect($entries)->toHaveCount(1)
        ->and(
            collect($entries)->map(fn ($entry) => $entry->title()->value())->toArray()
        )->toBe([
            'Both',
        ]);
});
