<?php

use Illuminate\Support\Str;
use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Facades\Feedamic as FeedamicFacade;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\YAML;

it('correctly and conditionally uses modifiers', function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

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

    // disable bard sets
    FeedamicEntry::ignoreBardSets(true);

    $feed = FeedamicFacade::render($config, '/content/feed/atom');
    $fixture = file_get_contents(__DIR__.'/../../__fixtures__/feeds/content_modifiers.xml.stub');

    expect(Str::squish($feed))->toContain(Str::squish($fixture));

    // add bard modifiers
    FeedamicEntry::ignoreBardSets(false);

    FeedamicFacade::modify(
        fieldHandle: 'content',
        modifier: function (AbstractFeedamicEntry $entry, $value) {
            return view('feedamic.content', [
                'content' => $value,
            ]);
        }
    );

    $feed = FeedamicFacade::render($config, '/content/feed/atom');
    $fixture = file_get_contents(__DIR__.'/../../__fixtures__/feeds/content_modifiers_sets.xml.stub');

    expect(Str::squish($feed))->toContain(Str::squish($fixture));
});
