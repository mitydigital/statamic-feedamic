<?php

use MityDigital\Feedamic\Facades\Feedamic;

beforeEach(function () {
    $this->route = '/feed/atom';
    $this->config = Feedamic::getConfig($this->route, 'default');
});
it('correctly caches when enabled', function () {
    config(['feedamic.cache_enabled' => true]);
    expect(\Illuminate\Support\Facades\Cache::get('feedamic.all-sites.default.atom'))->toBeNull();
    Feedamic::render($this->config, $this->route);
    expect(\Illuminate\Support\Facades\Cache::get('feedamic.all-sites.default.atom'))->not()->toBeNull();
});

it('does not cache when disabled', function () {
    expect(\Illuminate\Support\Facades\Cache::get('feedamic.all-sites.default.atom'))->toBeNull();
    Feedamic::render($this->config, $this->route);
    expect(\Illuminate\Support\Facades\Cache::get('feedamic.all-sites.default.atom'))->toBeNull();
});

it('clears all by default', function () {
    $cleared = Feedamic::clearCache();
    expect($cleared)->toHaveCount(7);
});

it('correctly selectively clears feed handles', function () {
    $cleared = Feedamic::clearCache(['all-sites']);
    expect($cleared)->toHaveCount(6);

    $cleared = Feedamic::clearCache(['ca-site']);
    expect($cleared)->toHaveCount(1);
});

it('correctly selectively clears sites', function () {
    $cleared = Feedamic::clearCache(sites: ['ca']);
    expect($cleared)->toHaveCount(3);

    $cleared = Feedamic::clearCache(sites: ['us']);
    expect($cleared)->toHaveCount(2);

    $cleared = Feedamic::clearCache(sites: ['ca', 'us']);
    expect($cleared)->toHaveCount(5);
});

it('correctly selectively clears collections', function () {
    $cleared = Feedamic::clearCache(collection: 'blog');
    expect($cleared)->toHaveCount(7);

    $cleared = Feedamic::clearCache(collection: 'pages');
    expect($cleared)->toHaveCount(0);
});
