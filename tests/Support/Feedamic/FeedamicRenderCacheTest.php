<?php

use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\YAML;

beforeEach(function () {
    $default = collect(YAML::file(base_path('content/feedamic.yaml'))->parse());

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

it('does not cache when caching is disabled', function () {
    expect(config('feedamic.cache_enabled'))->toBe(false);

    $cacheKey = $this->config->getCacheKey($this->route, 'default');

    Feedamic::render($this->config, $this->route);

    expect(Cache::get($cacheKey))
        ->toBeNull();
});

it('does stores in the cache when caching is enabled', function () {
    \Statamic\Facades\Config::set('feedamic.cache_enabled', true);

    expect(config('feedamic.cache_enabled'))->toBe(true);

    $cacheKey = $this->config->getCacheKey($this->route, 'default');

    $render = Feedamic::render($this->config, $this->route);

    expect(Cache::get($cacheKey))
        ->not()->toBeNull()
        ->toBe($render);
});
