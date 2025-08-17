<?php

use Illuminate\Support\Str;
use Illuminate\View\ViewException;
use MityDigital\Feedamic\Facades\Feedamic as FeedamicFacade;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\YAML;

it('correctly and conditionally uses modifiers with no sets or sets excluded', function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    FeedamicEntry::ignoreBardSets(true);

    // add the "content" feed
    FeedamicFacade::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'content',
                'title' => 'Content Types',
                'description' => 'Testing of Content Types and modifiers',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/content/feed/atom',
                    'rss' => '/content/feed',
                ],
                'collections' => [
                    'feed_content_types',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'default',
                    'author_mode' => 'default',
                    'content_mode' => 'custom',
                    'content' => ['content'],
                ],
            ],
        ],
        'default_summary' => [],
    ]));
    FeedamicFacade::load(true);

    // get the config
    $config = FeedamicFacade::getConfig('/content/feed/atom', 'default');

    $feed = FeedamicFacade::render($config, '/content/feed/atom');
    $fixture = file_get_contents(__DIR__.'/../../__fixtures__/feeds/content_modifiers.xml.stub');

    expect(Str::squish($feed))->toContain(Str::squish($fixture));
});

it('correctly and conditionally throws an exception when sets are present but not processed', function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    FeedamicEntry::ignoreBardSets(false);

    // add the "content" feed
    FeedamicFacade::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'content',
                'title' => 'Content Types',
                'description' => 'Testing of Content Types and modifiers',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/content/feed/atom',
                    'rss' => '/content/feed',
                ],
                'collections' => [
                    'feed_content_types',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'default',
                    'author_mode' => 'default',
                    'content_mode' => 'custom',
                    'content' => ['content'],
                ],
            ],
        ],
        'default_summary' => [],
    ]));
    FeedamicFacade::load(true);

    // get the config
    $config = FeedamicFacade::getConfig('/content/feed/atom', 'default');

    $feed = FeedamicFacade::render($config, '/content/feed/atom');
})->throws(ViewException::class);
