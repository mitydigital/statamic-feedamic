<?php

use App\Models\MyCustomFeedamicAuthor;
use App\Models\MyCustomFeedamicEntry;
use App\Scopes\MyQueryScope;
use MityDigital\Feedamic\Models\FeedamicAuthor;
use MityDigital\Feedamic\Models\FeedamicConfig;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\Site;

beforeEach(function () {
    Site::setSites([
        'default' => [
            'name' => 'Australia',
            'locale' => 'en_AU',
            'url' => '/',
        ],
    ]);
    $this->defaults = collect([
        'default_title' => [
            'title',
        ],
        'default_summary' => [
            'introduction',
            'content',
        ],
        'default_content' => [
            'content',
        ],
        'default_image_enabled' => true,
        'default_image' => [
            'image',
        ],
        'default_image_width' => 1280,
        'default_image_height' => 720,
        'default_author_fallback_name' => 'Polly Waffle',
        'default_author_fallback_email' => 'polly@waffle.com.au',
        'default_author_enabled' => true,
        'default_author_type' => 'entry',
        'default_author_field' => 'author_entry',
        'default_author_name' => '[name_first] [name_last]',
        'default_author_email' => 'email',
        'default_copyright' => 'Default Copyright Statement',
        'default_entry_model' => FeedamicEntry::class,
        'default_author_model' => FeedamicAuthor::class,
    ]);
});

it('correctly sets the handle during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'my-handle',
    ], $this->defaults);

    expect($config->handle)->toBe('my-handle');
});

it('correctly sets the title during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'title-test',
        'title' => 'My title',
    ], $this->defaults);

    expect($config->title)->toBe('My title');

    // can be null
    $config = new FeedamicConfig([
        'handle' => 'title-test',
        'description' => null,
    ], $this->defaults);

    expect($config->title)->toBeNull();
});

it('correctly sets the description during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'title-test',
        'description' => 'My description',
    ], $this->defaults);

    expect($config->description)->toBe('My description');

    // can be null
    $config = new FeedamicConfig([
        'handle' => 'description-test',
        'description' => null,
    ], $this->defaults);

    expect($config->description)->toBeNull();
});

it('correctly sets the collections during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'title-test',
        'collections' => ['blog'],
    ], $this->defaults);

    expect($config->collections)->toBeArray()
        ->toBe(['blog']);
});

it('correctly sets the alt url during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'alt_url-test',
        'alt_url' => 'http://www.google.com',
    ], $this->defaults);

    expect($config->alt_url)->toBe('http://www.google.com');

    // can be null
    $config = new FeedamicConfig([
        'handle' => 'title-test',
        'alt_url' => null,
    ], $this->defaults);

    expect($config->alt_url)->toBeNull();
});

it('correctly sets the feedamic author model during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'author_model-test',
    ], $this->defaults);

    expect($config->author_model)->toBe(FeedamicAuthor::class);

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'author_model-test',
        'author_model' => MyCustomFeedamicAuthor::class,
    ], $this->defaults);

    expect($config->author_model)->toBe(MyCustomFeedamicAuthor::class);
});

it('correctly sets the feedamic entry model during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'entry_model-test',
    ], $this->defaults);

    expect($config->entry_model)->toBe(FeedamicEntry::class);

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'entry_model-test',
        'entry_model' => MyCustomFeedamicEntry::class,
    ], $this->defaults);

    expect($config->entry_model)->toBe(MyCustomFeedamicEntry::class);
});

it('correctly sets the scope during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'entry_scope-test',
    ], $this->defaults);

    expect($config->scope)->toBeNull();

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'entry_scope-test',
        'scope' => MyQueryScope::class,
    ], $this->defaults);

    expect($config->scope)->toBe(MyQueryScope::class);
});

it('correctly sets the author fallback name during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'author_fallback_name-test',
    ], $this->defaults);

    expect($config->author_fallback_name)->toBe($this->defaults->get('default_author_fallback_name'));

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'author_fallback_name-test',
        'author_fallback_mode' => 'custom',
        'author_fallback_name' => 'Cherry Ripe',
    ], $this->defaults);

    expect($config->author_fallback_name)->toBe('Cherry Ripe');
});
it('correctly sets the author fallback email during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'author_fallback_email-test',
    ], $this->defaults);

    expect($config->author_fallback_email)->toBe($this->defaults->get('default_author_fallback_email'));

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'author_fallback_email-test',
        'author_fallback_mode' => 'custom',
        'author_fallback_email' => 'cherry@ripe.com.au',
    ], $this->defaults);

    expect($config->author_fallback_email)->toBe('cherry@ripe.com.au');

    // can be empty
    $config = new FeedamicConfig([
        'handle' => 'author_fallback_email-test',
        'author_fallback_mode' => 'custom',
        'author_fallback_email' => '',
    ], $this->defaults);

    expect($config->author_fallback_email)->toBe('');
});

it('correctly sets the taxonomies during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'taxonomies-test',
    ], $this->defaults);

    expect($config->taxonomies)
        ->toBeArray()
        ->toHaveCount(0);

    // can be overridden
    $config = new FeedamicConfig([
        'handle' => 'taxonomies-test',
        'taxonomies' => [
            [
                'terms' => ['product'],
                'logic' => 'and',
            ],
        ],
    ], $this->defaults);

    expect($config->taxonomies)
        ->toBeArray()
        ->toHaveCount(1);
});

it('correctly sets the limit during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'limit-test',
    ], $this->defaults);

    expect($config->limit)->toBeNull();

    // can be overridden - all
    $config = new FeedamicConfig([
        'handle' => 'limit-test',
        'show' => 'all',
    ], $this->defaults);

    expect($config->limit)->toBeNull();

    // can be overridden - set
    $config = new FeedamicConfig([
        'handle' => 'limit-test',
        'show' => 'limit',
        'show_limit' => 25,
    ], $this->defaults);

    expect($config->limit)->toBe(25);
});

it('correctly sets the sites during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'sites-test',
    ], $this->defaults);

    // default to nothing
    expect($config->sites)
        ->toBeArray()
        ->toHaveCount(0);

    // specific sites
    $config = new FeedamicConfig([
        'handle' => 'sites-test',
        'sites' => 'specific',
        'sites_specific' => [
            'au',
            'us',
        ],
    ], $this->defaults);

    expect($config->sites)
        ->toBeArray()
        ->toHaveCount(2)
        ->toBe([
            'au',
            'us',
        ]);

    // all sites
    $config = new FeedamicConfig([
        'handle' => 'sites-test',
        'sites' => 'all',
    ], $this->defaults);

    expect($config->sites)
        ->toBeArray()
        ->toHaveCount(1)
        ->toBe(['default']);
});

it('correctly sets the routes and route views during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
    ], $this->defaults);

    // default to nothing
    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => null,
            'atom_view' => null,
            'rss' => null,
            'rss_view' => null,
        ]);

    // set atom route
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
        ],
    ], $this->defaults);

    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => '/feed/atom',
            'atom_view' => 'feedamic::atom',
            'rss' => null,
            'rss_view' => null,
        ]);

    // set rss route
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'rss' => '/feed/rss',
        ],
    ], $this->defaults);

    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => null,
            'atom_view' => null,
            'rss' => '/feed/rss',
            'rss_view' => 'feedamic::rss',
        ]);

    // set view, but not route (does not update view)
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom_view' => 'my-atom-view',
        ],
    ], $this->defaults);

    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => null,
            'atom_view' => null,
            'rss' => null,
            'rss_view' => null,
        ]);

    // override the view
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
            'atom_view' => 'my-atom-view',
        ],
    ], $this->defaults);

    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => '/feed/atom',
            'atom_view' => 'my-atom-view',
            'rss' => null,
            'rss_view' => null,
        ]);

    // set it all
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
            'atom_view' => 'my-atom-view',
            'rss' => '/feed/rss',
            'rss_view' => 'my-rss-view',
        ],
    ], $this->defaults);

    expect($config->routes)
        ->toBeArray()
        ->toBe([
            'atom' => '/feed/atom',
            'atom_view' => 'my-atom-view',
            'rss' => '/feed/rss',
            'rss_view' => 'my-rss-view',
        ]);
});

it('correctly sets the copyright during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'copyright-test',
        'copyright_mode' => 'unexpected',
    ], $this->defaults);

    expect($config->copyright)->toBeNull();

    // custom
    $config = new FeedamicConfig([
        'handle' => 'copyright-test',
        'copyright_mode' => 'custom',
        'copyright' => 'Custom Copyright',
    ], $this->defaults);

    expect($config->copyright)->toBe('Custom Copyright');

    // default
    $config = new FeedamicConfig([
        'handle' => 'copyright-test',
        'copyright_mode' => 'default',
        'copyright' => 'Custom Copyright',
    ], $this->defaults);

    expect($config->copyright)->toBe('Default Copyright Statement');
});

it('correctly maps the title during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'map_title-test',
        'mappings' => [
            'title_mode' => 'default',
            'title' => [
                'custom_title',
            ],
        ],
    ], $this->defaults);

    expect($config->getTitleMappings())->toBe($this->defaults['default_title']);

    // override
    $config = new FeedamicConfig([
        'handle' => 'map_title-test',
        'mappings' => [
            'title_mode' => 'custom',
            'title' => [
                'custom_title',
            ],
        ],
    ], $this->defaults);

    expect($config->getTitleMappings())->toBe([
        'custom_title',
    ]);
});

it('correctly maps the summary during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'map_summary-test',
        'mappings' => [
            'summary_mode' => 'default',
            'summary' => [
                'custom_summary',
            ],
        ],
    ], $this->defaults);

    expect($config->getSummaryMappings())
        ->toBe($this->defaults['default_summary'])
        ->and($config->hasSummary())->toBeTrue();

    // override
    $config = new FeedamicConfig([
        'handle' => 'map_summary-test',
        'mappings' => [
            'summary_mode' => 'custom',
            'summary' => [
                'custom_summary',
            ],
        ],
    ], $this->defaults);

    expect($config->getSummaryMappings())
        ->toBe(['custom_summary'])
        ->and($config->hasSummary())->toBeTrue();

    // disable
    $config = new FeedamicConfig([
        'handle' => 'map_summary-test',
        'mappings' => [
            'summary_mode' => 'disabled',
            'summary' => [
                'custom_summary',
            ],
        ],
    ], $this->defaults);

    expect($config->getSummaryMappings())
        ->toBeArray()
        ->toHaveCount(0)
        ->and($config->hasSummary())->toBeFalse();
});

it('correctly maps the content during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'map_content-test',
        'mappings' => [
            'content_mode' => 'default',
            'content' => [
                'custom_content',
            ],
        ],
    ], $this->defaults);

    expect($config->getContentMappings())
        ->toBe($this->defaults['default_content'])
        ->and($config->hasContent())->toBeTrue();

    // override
    $config = new FeedamicConfig([
        'handle' => 'map_content-test',
        'mappings' => [
            'content_mode' => 'custom',
            'content' => [
                'custom_content',
            ],
        ],
    ], $this->defaults);

    expect($config->getContentMappings())->toBe([
        'custom_content',
    ])
        ->and($config->hasContent())->toBeTrue();

    // disable
    $config = new FeedamicConfig([
        'handle' => 'map_content-test',
        'mappings' => [
            'content_mode' => 'disabled',
            'content' => [
                'custom_content',
            ],
        ],
    ], $this->defaults);

    expect($config->getContentMappings())
        ->toBeArray()
        ->toHaveCount(0)
        ->and($config->hasContent())->toBeFalse();
});

it('correctly maps the image during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'map_image-test',
        'mappings' => [
            'image_mode' => 'default',
            'image' => [
                'custom_image',
            ],
            'image_dimensions_mode' => 'default',
            'image_width' => 200,
            'image_height' => 100,
        ],
    ], $this->defaults);

    expect($config->getImageMappings())->toBe($this->defaults['default_image'])
        ->and($config->getImageWidth())->toBe($this->defaults['default_image_width'])
        ->and($config->getImageHeight())->toBe($this->defaults['default_image_height'])
        ->and($config->hasImage())->toBeTrue();

    // disabled
    $config = new FeedamicConfig([
        'handle' => 'map_image-test',
        'mappings' => [
            'image_mode' => 'disabled',
            'image' => [
                'custom_image',
            ],
            'image_dimensions_mode' => 'custom',
            'image_width' => 200,
            'image_height' => 100,
        ],
    ], $this->defaults);

    expect($config->getImageMappings())
        ->toBeArray()
        ->toHaveCount(0)
        ->and($config->getImageWidth())->toBeNull()
        ->and($config->getImageHeight())->toBeNull()
        ->and($config->hasImage())->toBeFalse();

    // override image only
    $config = new FeedamicConfig([
        'handle' => 'map_image-test',
        'mappings' => [
            'image_mode' => 'custom',
            'image' => [
                'custom_image',
            ],
            // 'image_dimensions_mode' => 'custom',
            // 'image_width' => 200,
            // 'image_height' => 100,
        ],
    ], $this->defaults);

    expect($config->getImageMappings())->toBe(['custom_image'])
        ->and($config->getImageWidth())->toBe($this->defaults['default_image_width'])
        ->and($config->getImageHeight())->toBe($this->defaults['default_image_height'])
        ->and($config->hasImage())->toBeTrue();

    // override width and height too
    $config = new FeedamicConfig([
        'handle' => 'map_image-test',
        'mappings' => [
            'image_mode' => 'custom',
            'image' => [
                'custom_image',
            ],
            'image_dimensions_mode' => 'custom',
            'image_width' => 200,
            'image_height' => 100,
        ],
    ], $this->defaults);

    expect($config->getImageMappings())->toBe(['custom_image'])
        ->and($config->getImageWidth())->toBe(200)
        ->and($config->getImageHeight())->toBe(100)
        ->and($config->hasImage())->toBeTrue();
});

it('correctly maps the author during creation', function () {
    $config = new FeedamicConfig([
        'handle' => 'map_author-test',
        'mappings' => [
            'author_mode' => 'default',
            'author_type' => 'entry',
            'author_field' => 'custom_entry',
            'author_name' => 'custom_name',
            'author_email' => 'custom_email',
        ],
    ], $this->defaults);

    expect($config->getAuthorType())->toBe($this->defaults['default_author_type'])
        ->and($config->getAuthor())->toBe($this->defaults['default_author_field'])
        ->and($config->getAuthorName())->toBe($this->defaults['default_author_name'])
        ->and($config->getAuthorEmail())->toBe($this->defaults['default_author_email'])
        ->and($config->hasAuthor())->toBeTrue();

    // disabled
    $config = new FeedamicConfig([
        'handle' => 'map_author-test',
        'mappings' => [
            'author_mode' => 'disabled',
            'author_type' => 'entry',
            'author_field' => 'custom_entry',
            'author_name' => 'custom_name',
            'author_email' => 'custom_email',
        ],
    ], $this->defaults);

    expect($config->getAuthorType())->toBeNull()
        ->and($config->getAuthor())->toBeNull()
        ->and($config->getAuthorName())->toBeNull()
        ->and($config->getAuthorEmail())->toBeNull()
        ->and($config->hasAuthor())->toBeFalse();

    // custom - entry
    $config = new FeedamicConfig([
        'handle' => 'map_author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'entry',
            'author_field' => 'custom_entry',
            'author_name' => 'custom_name',
            'author_email' => 'custom_email',
        ],
    ], $this->defaults);

    expect($config->getAuthorType())->toBe('entry')
        ->and($config->getAuthor())->toBe('custom_entry')
        ->and($config->getAuthorName())->toBe('custom_name')
        ->and($config->getAuthorEmail())->toBe('custom_email')
        ->and($config->hasAuthor())->toBeTrue();

    $config = new FeedamicConfig([
        'handle' => 'map_author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'field',
            'author_field' => 'custom_field',
            'author_name' => 'custom_field_name',
            'author_email' => 'custom_field_email',
        ],
    ], $this->defaults);

    expect($config->getAuthorType())->toBe('field')
        ->and($config->getAuthor())->toBe('custom_field')
        ->and($config->getAuthorName())->toBe('custom_field_name')
        ->and($config->getAuthorEmail())->toBe('custom_field_email')
        ->and($config->hasAuthor())->toBeTrue();
});

it('correctly determines if a route exists', function () {
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
        ],
    ], $this->defaults);

    expect($config->hasRoute('/feed/atom'))->toBeTrue()
        ->and($config->hasRoute('not-the-route'))->toBeFalse();
});

it('correctly gets an array of routes for the feed', function () {
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
            'rss' => '/feed/rss',
        ],
    ], $this->defaults);

    expect($config->getRoutes())
        ->toBeArray()
        ->toHaveCount(2)
        ->toBe([
            'atom' => '/feed/atom',
            'rss' => '/feed/rss',
        ]);

    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'rss' => '/feed/rss',
        ],
    ], $this->defaults);

    expect($config->getRoutes())
        ->toBeArray()
        ->toHaveCount(1)
        ->toBe([
            'rss' => '/feed/rss',
        ]);
});

it('can return the route for a given feed type', function () {
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'atom' => '/feed/atom',
            'rss' => '/feed/rss',
        ],
    ], $this->defaults);

    expect($config->getRouteForFeedType('atom'))->toBe('/feed/atom')
        ->and($config->getRouteForFeedType('rss'))->toBe('/feed/rss');

    // only one route
    $config = new FeedamicConfig([
        'handle' => 'routes-test',
        'routes' => [
            'rss' => '/feed/rss',
        ],
    ], $this->defaults);

    expect($config->getRouteForFeedType('atom'))->toBeNull()
        ->and($config->getRouteForFeedType('rss'))->toBe('/feed/rss');
});
