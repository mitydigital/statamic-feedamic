<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use MityDigital\Feedamic\Support\Feedamic;
use Statamic\Facades\Site;
use Statamic\Fields\Blueprint;

it('has the correct feed types', function () {
    expect(app(Feedamic::class)->getFeedTypes())
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain('rss', 'atom');
});

it('returns the configured path', function () {
    // get the default route
    expect(app(Feedamic::class)->getPath())
        ->toBe(base_path('content').'/feedamic.yaml');

    //
    // allow configurations
    //
    // path
    config()->set('feedamic.path', storage_path('app'));
    expect(app(Feedamic::class)->getPath())
        ->toBe(storage_path('app').'/feedamic.yaml');

    // filename
    config()->set('feedamic.filename', 'custom');
    expect(app(Feedamic::class)->getPath())
        ->toBe(storage_path('app').'/custom.yaml');
});

it('saves the config', function () {
    // file doesn't exist
    expect(File::exists(app(Feedamic::class)->getPath()))
        ->toBeFalse();

    // save
    app(Feedamic::class)->save([
        'a' => 'b',
    ]);

    // file exists
    expect(File::exists(app(Feedamic::class)->getPath()))
        ->toBeTrue();

    expect(File::get(app(Feedamic::class)->getPath()))
        ->toBe("a: b\n");
});

it('loads the config', function () {
    app(Feedamic::class)->save([
        'foo' => 'bar',
    ]);

    expect(app(Feedamic::class)->load())
        ->toBeInstanceOf(Collection::class)
        ->toArray()
        ->toBe([
            'foo' => 'bar',
        ]);
});

it('loads the blueprint', function () {
    $blueprint = app(Feedamic::class)->blueprint();
    expect($blueprint)->toBeInstanceOf(Blueprint::class)
        ->and($blueprint->handle())->toBe('config')
        ->and($blueprint->namespace())->toBe('feedamic');
});

it('ensures each route starts with a slash', function () {
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

it('has the correct validation for the field in the blueprint', function (string $handle, array $rules) {
    $blueprint = app(Feedamic::class)->blueprint();

    expect($blueprint->field($handle)->config()['validate'])
        ->toBeArray()
        ->toHaveCount(count($rules))
        ->toBe($rules);
})->with([
    'feeds' => [
        'feeds',
        [
            'required',
            'array',
            'min:1',
        ],
    ],
    'default title' => [
        'default_title',
        [
            'required',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default summary' => [
        'default_summary',
        [
            'nullable',
            'array',
            'min:0',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default image' => [
        'default_image',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default image width' => [
        'default_image_width',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'integer',
            'min:1',
        ],
    ],
    'default image height' => [
        'default_image_height',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'integer',
            'min:1',
        ],
    ],
    'default author name' => [
        'default_author_name',
        [
            'exclude_unless:{this}.default_author_enabled,true',
            'required',
            'new \MityDigital\Feedamic\Rules\AuthorNameRule(default_author_type)',
        ],
    ],
    'default author email' => [
        'default_author_email',
        [
            'nullable',
            'new \Statamic\Rules\Slug',
        ],
    ],
    'default author model' => [
        'default_author_model',
        [
            'required',
        ],
    ],
    'default entry model' => [
        'default_entry_model',
        [
            'required',
        ],
    ],
]);

it('has the correct validation for the feeds array in the blueprint', function (string $handle, array $rules) {
    $blueprint = app(Feedamic::class)->blueprint();

    $fields = collect(Arr::get($blueprint->field('feeds')->config(), 'sets.types.sets.feed.fields', []))
        ->mapWithKeys(fn (array $field) => [$field['handle'] => $field['field']]);

    if (Str::contains($handle, '.')) {
        $outerField = Str::before($handle, '.');
        $innerField = Str::after($handle, '.');
        $field = Arr::get($fields, $outerField);
        $field = collect($field['fields'])
            ->mapWithKeys(fn (array $field) => [$field['handle'] => $field['field']])
            ->get($innerField);
    } else {
        $field = Arr::get($fields, $handle);
    }
    $validate = Arr::get($field, 'validate');

    expect($validate)
        ->toBeArray()
        ->toHaveCount(count($rules))
        ->toBe($rules);
})->with([
    'sites' => [
        'sites',
        [
            'required',
        ],
    ],
    'sites specific' => [
        'sites_specific',
        [
            'exclude_unless:{this}.sites,specific',
            'min:1',
        ],
    ],
    'title' => [
        'title',
        [
            'required',
        ],
    ],
    'handle' => [
        'handle',
        [
            'required',
            'new \MityDigital\Feedamic\Rules\HandleMustBeUniqueRule',
        ],
    ],
    'description' => [
        'description',
        [
            'required',
        ],
    ],
    'routes' => [
        'routes',
        [
            'new \MityDigital\Feedamic\Rules\RequiresAtLeastOneRouteRule',
        ],
    ],
    'routes atom' => [
        'routes.atom',
        [
            'nullable',
            'new \MityDigital\Feedamic\Rules\FeedRouteFormatRule',
        ],
    ],
    'routes rss' => [
        'routes.rss',
        [
            'nullable',
            'new \MityDigital\Feedamic\Rules\FeedRouteFormatRule',
        ],
    ],
    'collections' => [
        'collections',
        [
            'required',
            'new \MityDigital\Feedamic\Rules\CollectionsOrderedTheSameWayRule',
        ],
    ],
    'taxonomies terms' => [
        'taxonomies.terms',
        ['required', 'min:1'],
    ],
    'taxonomies logic' => [
        'taxonomies.logic',
        ['required'],
    ],
    'show limit' => [
        'show_limit',
        [
            'sometimes',
            'min:1',
            'integer',
        ],
    ],
    'mappings title' => [
        'mappings.title',
        [
            'exclude_unless:{this}.mappings.title_mode,custom',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings summary' => [
        'mappings.summary',
        [
            'exclude_unless:{this}.mappings.summary_mode,custom',
            'array',
            'min:0',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings image' => [
        'mappings.image',
        [
            'sometimes',
            'exclude_unless:{this}.mappings.image_mode,custom',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings image width' => [
        'mappings.image_width',
        [
            'exclude_unless:{this}.mappings.image_dimensions_mode,custom',
            'integer',
            'min:1',
        ],
    ],
    'mappings image height' => [
        'mappings.image_height',
        [
            'exclude_unless:{this}.mappings.image_dimensions_mode,custom',
            'integer',
            'min:1',
        ],
    ],
    'mappings author type' => [
        'mappings.author_type',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
        ],
    ],
    'mappings author name' => [
        'mappings.author_name',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
            'new \MityDigital\Feedamic\Rules\AuthorNameRule',
        ],
    ],
    'mappings author email' => [
        'mappings.author_email',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
            'nullable',
            'new \Statamic\Rules\Slug',
        ],
    ],
    'copyright' => [
        'copyright',
        [
            'exclude_unless:{this}.copyright_mode,custom',
            'string',
        ],
    ],
    'author_model' => [
        'author_model',
        [
            'required',
        ],
    ],
    'entry_model' => [
        'entry_model',
        [
            'required',
        ],
    ],
    'alt_url' => [
        'alt_url',
        [
            'url',
        ],
    ],
]);
