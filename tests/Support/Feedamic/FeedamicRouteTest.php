<?php

use MityDigital\Feedamic\Http\Controllers\FeedamicController;
use MityDigital\Feedamic\Support\Feedamic;
use Statamic\Facades\Site;

it('ensures each route starts with a slash', function () {
    Site::setSites([
        'default' => [
            'name' => 'Australia',
            'locale' => 'en_AU',
            'url' => '/',
        ],
    ]);
    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'slasher',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => 'feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();

    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(0)
        ->and($routes['default'])->toBeArray()->toHaveCount(2)
        ->toBe(['/feed/atom', '/feed']);
});

it('correctly creates the routes for a single site', function () {
    Site::setSites([
        'default' => [
            'name' => 'Australia',
            'locale' => 'en_AU',
            'url' => '/',
        ],
    ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'routes',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(0)
        ->and($routes['default'])->toBeArray()->toHaveCount(2)
        ->toBe(['/feed/atom', '/feed']);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'routes',
                'sites' => 'all',
                'routes' => [
                    'atom' => null,
                    'rss' => '/feed.rss',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(0)
        ->and($routes['default'])->toBeArray()->toHaveCount(1)
        ->toBe(['/feed.rss']);
});

it('correctly creates the routes for multiple sites on a single domain', function () {
    Site::setSites([
        'en_AU' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_AU',
            'url' => '{{ config:app:url }}/',
        ],
        'en_US' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_US',
            'url' => '{{ config:app:url }}/us/',
        ],
    ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'all-sites',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(0)
        ->and($routes['default'])->toBeArray()->toHaveCount(4)
        ->toBe([
            '/feed/atom',
            '/feed',
            '/us/feed/atom',
            '/us/feed',
        ]);
});

it('correctly creates the routes for a single site on a multi site setup on a single domain', function () {
    Site::setSites([
        'en_AU' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_AU',
            'url' => '{{ config:app:url }}/',
        ],
        'en_US' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_US',
            'url' => '{{ config:app:url }}/us/',
        ],
    ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'specific-sites',
                'sites_specific' => ['en_US'],
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(0)
        ->and($routes['default'])->toBeArray()->toHaveCount(2)
        ->toBe([
            '/us/feed/atom',
            '/us/feed',
        ]);
});

it('correctly creates the routes for multiple sites on multiple domains', function () {
    Site::setSites([
        'en_AU' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_AU',
            'url' => '{{ config:app:url }}/',
        ],
        'en_US' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_US',
            'url' => '{{ config:app:url }}/us/',
        ],
        'en_CA' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_CA',
            'url' => 'http://en_ca.test/',
        ],
    ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'all-sites',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(1)
        ->toBe([
            'http://en_ca.test' => [
                '/feed/atom',
                '/feed',
            ],
        ])
        ->and($routes['default'])->toBeArray()->toHaveCount(4)
        ->toBe([
            '/feed/atom',
            '/feed',
            '/us/feed/atom',
            '/us/feed',
        ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'all-sites',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
            [
                'handle' => 'specific-sites',
                'sites_specific' => ['en_AU'],
                'routes' => [
                    'atom' => '/feed/another/atom',
                    'rss' => '/feed/another',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(1)
        ->toBe([
            'http://en_ca.test' => [
                '/feed/atom',
                '/feed',
            ],
        ])
        ->and($routes['default'])->toBeArray()->toHaveCount(6)
        ->toBe([
            '/feed/atom',
            '/feed',
            '/feed/another/atom',
            '/feed/another',
            '/us/feed/atom',
            '/us/feed',
        ]);
});

it('correctly creates the routes for complex configurations of multiple sites on multiple domains', function () {
    Site::setSites([
        'en_AU' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_AU',
            'url' => '{{ config:app:url }}/',
        ],
        'en_US' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_US',
            'url' => '{{ config:app:url }}/us/',
        ],
        'en_CA' => [
            'name' => '{{ config:app:name }}',
            'locale' => 'en_CA',
            'url' => 'http://en_ca.test/',
        ],
    ]);

    app(Feedamic::class)->save([
        'feeds' => [
            [
                'handle' => 'au',
                'sites_specific' => ['en_AU'],
                'routes' => [
                    'rss' => '/au/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
            [
                'handle' => 'us',
                'sites_specific' => ['en_US'],
                'routes' => [
                    'rss' => '/test-us/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
            [
                'handle' => 'ca',
                'sites_specific' => ['en_CA'],
                'routes' => [
                    'rss' => '/ca/feed',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'image_mode' => 'default',
                ],
            ],
        ],
    ]);

    $routes = app(Feedamic::class)->getRoutes();
    expect($routes)->toBeArray()->toHaveCount(2)
        ->and($routes['domains'])->toBeArray()->toHaveCount(1)
        ->toBe([
            'http://en_ca.test' => [
                '/ca/feed',
            ],
        ])
        ->and($routes['default'])->toBeArray()->toHaveCount(2)
        ->toBe([
            '/au/feed',
            '/us/test-us/feed',
        ]);
});

it('registers the expected routes', function () {
    $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
        ->filter(fn (\Illuminate\Routing\Route $route) => $route->getControllerClass() === FeedamicController::class)
        ->map(fn (\Illuminate\Routing\Route $route) => $route->getDomain().'/'.$route->uri())
        ->values();

    $expected = [
        '/feed/atom',
        '/feed',
        '/us/feed/atom',
        '/us/feed',
        'ca.test/feed/atom',
        'ca.test/feed',
        'ca.test/ca/feed/atom',
    ];

    expect($routes)->toHaveCount(7);
    foreach ($expected as $route) {
        expect($routes)->toContain($route);
    }
});
