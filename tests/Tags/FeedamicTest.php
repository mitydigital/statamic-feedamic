<?php

use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Tags\Feedamic as FeedamicTag;
use Statamic\Facades\Site;

it('returns nothing when there is no configuration', function () {
    $tag = new FeedamicTag;

    expect($tag->index())->toBe('')
        ->and($tag->wildcard('blog'))->toBe('');
});

it('returns an atom feed when there is only an atom feed configured', function () {
    Feedamic::save([
        'feeds' => [
            [
                'sites' => 'all',
                'handle' => 'atom',
                'title' => 'Atom Test',
                'description' => 'Atom Test',
                'routes' => [
                    'atom' => '/feed/atom',
                ],
                'copyright_mode' => 'custom',
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'disabled',
                    'author_mode' => 'disabled',
                ],
            ],
        ],
    ]);

    $tag = new FeedamicTag;

    expect($tag->index())
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Atom Test" href="http://localhost/feed/atom" />')
        ->and($tag->wildcard('missing-handle'))->toBe('')
        ->and($tag->wildcard('atom'))
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Atom Test" href="http://localhost/feed/atom" />');
});

it('returns an rss feed when there is only an rss feed configured', function () {
    Feedamic::save([
        'feeds' => [
            [
                'sites' => 'all',
                'handle' => 'rss',
                'title' => 'RSS Test',
                'description' => 'RSS Test',
                'routes' => [
                    'rss' => '/feed',
                ],
                'copyright_mode' => 'custom',
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'disabled',
                    'author_mode' => 'disabled',
                ],
            ],
        ],
    ]);

    $tag = new FeedamicTag;

    expect($tag->index())
        ->toBe('<link rel="alternate" type="application/rss+xml" title="RSS Test" href="http://localhost/feed" />')
        ->and($tag->wildcard('missing-handle'))->toBe('')
        ->and($tag->wildcard('rss'))
        ->toBe('<link rel="alternate" type="application/rss+xml" title="RSS Test" href="http://localhost/feed" />');
});

it('returns both an atom and rss feed when both are configured', function () {
    Feedamic::save([
        'feeds' => [
            [
                'sites' => 'all',
                'handle' => 'both',
                'title' => 'Both Test',
                'description' => 'Both Test',
                'routes' => [
                    'atom' => '/feed/atom',
                    'rss' => '/feed',
                ],
                'copyright_mode' => 'custom',
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'disabled',
                    'author_mode' => 'disabled',
                ],
            ],
        ],
    ]);

    $tag = new FeedamicTag;

    expect($tag->index())
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Both Test" href="http://localhost/feed/atom" />'
            ."\r\n"
            .'<link rel="alternate" type="application/rss+xml" title="Both Test" href="http://localhost/feed" />')
        ->and($tag->wildcard('missing-handle'))->toBe('')
        ->and($tag->wildcard('both'))
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Both Test" href="http://localhost/feed/atom" />'
            ."\r\n"
            .'<link rel="alternate" type="application/rss+xml" title="Both Test" href="http://localhost/feed" />');
});

it('correctly returns routes for a specific site', function () {
    Feedamic::save([
        'feeds' => [
            [
                'sites' => 'specific',
                'sites_specific' => [
                    'us',
                ],
                'handle' => 'atom',
                'title' => 'Atom Test',
                'description' => 'Atom Test',
                'routes' => [
                    'atom' => '/feed/atom',
                ],
                'copyright_mode' => 'custom',
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'disabled',
                    'author_mode' => 'disabled',
                ],
            ],
        ],
    ]);

    Site::setSites([
        'au' => [
            'id' => 'au123',
            'handle' => 'au',
            'name' => 'AU',
            'locale' => 'en_AU',
            'url' => 'http://au.test/',
        ],
        'us' => [
            'id' => 'us123',
            'handle' => 'us',
            'name' => 'US',
            'locale' => 'en_US',
            'url' => 'http://us.test/',
        ],
    ])->save();

    $tag = new FeedamicTag;

    expect(Site::current())->handle()->toBe('au')
        ->and($tag->index())->toBe('')
        ->and($tag->wildcard('not-a-handle'))->toBe('')
        ->and($tag->wildcard('atom'))->toBe('');

    Site::setCurrent('us');

    expect(Site::current())->handle()->toBe('us')
        ->and($tag->index())
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Atom Test" href="http://us.test/feed/atom" />')
        ->and($tag->wildcard('not-a-handle'))->toBe('')
        ->and($tag->wildcard('atom'))
        ->toBe('<link rel="alternate" type="application/atom+xml" title="Atom Test" href="http://us.test/feed/atom" />');
});
