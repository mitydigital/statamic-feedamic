<?php

use Carbon\Carbon;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\YAML;

beforeEach(function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    $this->route = '/render-atom/feed/atom';

    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'render-atom',
                'title' => 'Atom Render Test',
                'description' => 'Testing the Atom rendering',
                'sites' => 'all',
                'routes' => [
                    'atom' => $this->route,
                ],
                'collections' => [
                    'blog',
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
    $this->config = Feedamic::getConfig($this->route, 'default');
});

it('has the expected feed skeleton', function () {
    // freeze time so the timestamp doesn't change
    $this->travelTo(Carbon::parse('2025-08-01 14:50:00'));
    $this->freezeTime();

    $render = Feedamic::render($this->config, $this->route);

    expect($render)->toBe(file_get_contents(__DIR__.'/../../__fixtures__/feeds/skeleton-atom.xml'));
});
