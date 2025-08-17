<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use MityDigital\Feedamic\Support\Feedamic;
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
    // force remove
    File::delete(base_path('content/feedamic.yaml'));

    // file doesn't exist
    expect(File::exists(app(Feedamic::class)->getPath()))
        ->toBeFalse();

    // save
    app(Feedamic::class)->save([
        'a' => 'b',
    ]);

    // file exists
    expect(File::exists(app(Feedamic::class)->getPath()))
        ->toBeTrue()
        ->and(File::get(app(Feedamic::class)->getPath()))
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
        ->and($blueprint->handle())->toBe('settings')
        ->and($blueprint->namespace())->toBe('feedamic');
});
