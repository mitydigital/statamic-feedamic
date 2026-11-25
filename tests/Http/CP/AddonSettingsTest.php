<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicAuthor;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Addons\Settings;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Facades\Addon;
use Statamic\Facades\Permission;
use Statamic\Facades\Role;

beforeEach(function () {
    if (statamicMajorVersion() < 6) {
        $this->markTestSkipped('Addon settings require Statamic 6.');
    }

    $this->settings = [
        'feeds' => [
            [
                'type' => 'feed', // replicator
                'handle' => 'content',
                'title' => 'Content',
                'description' => 'My Content Description',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/content/feed/atom',
                ],
                'collections' => ['feed_content_types'],
                'author_model' => FeedamicAuthor::class,
                'entry_model' => FeedamicEntry::class,
            ],
        ],
        'default_title' => ['heading', 'title'],
        'default_author_fallback_name' => 'Polly Waffle',
        'default_author_model' => FeedamicAuthor::class,
        'default_entry_model' => FeedamicEntry::class,
    ];

    // cache the rendered feed, and return the key
    $this->cacheFeed = function (): string {
        Feedamic::save($this->settings);

        $key = Feedamic::getConfig('/content/feed/atom', 'default')->getCacheKey('/content/feed/atom', 'default');
        Cache::put($key, 'Cached feed');

        return $key;
    };
});

it('does not include the statamic 5 cp routes or permission', function () {
    expect(Feedamic::includeCpRoutes())->toBeFalse()
        ->and(Route::has('statamic.cp.feedamic.config.show'))->toBeFalse()
        ->and(Route::has('statamic.cp.feedamic.config.update'))->toBeFalse()
        ->and(Permission::boot()->flattened()->map->value())->not()->toContain('feedamic.config');
});

it('can show the addon settings', function () {
    createSuperUser();

    $this->get(cp_route('addons.settings.edit', 'feedamic'))
        ->assertOk();
});

it('requires permission to view and update the addon settings', function () {
    $before = File::get(Feedamic::getPath());

    // not logged in
    $this->get(cp_route('addons.settings.edit', 'feedamic'))
        ->assertRedirect(cp_route('login'));

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), $this->settings)
        ->assertUnauthorized();

    // can access the cp, but not addon settings
    Role::make()->handle('cp-only')->permissions(['access cp'])->save();
    $user = createCpUser();
    $user->explicitRoles(['cp-only']);

    $this->get(cp_route('addons.settings.edit', 'feedamic'))
        ->assertRedirect(cp_route('index'));

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), $this->settings)
        ->assertForbidden();

    expect(File::get(Feedamic::getPath()))->toBe($before);

    // can configure addons
    Role::make()->handle('configure-addons')->permissions(['access cp', 'configure addons'])->save();
    $user->explicitRoles(['configure-addons']);

    $this->get(cp_route('addons.settings.edit', 'feedamic'))
        ->assertOk();

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), $this->settings)
        ->assertOk();
});

it('validates the addon settings', function () {
    createSuperUser();

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'feeds',
            'default_title',
            'default_author_fallback_name',
            'default_author_model',
            'default_entry_model',
        ]);
});

it('saves the addon settings where feedamic reads them', function () {
    createSuperUser();

    File::delete(Feedamic::getPath());

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), $this->settings)
        ->assertOk()
        ->assertJson(['saved' => true]);

    expect(File::exists(Feedamic::getPath()))->toBeTrue();

    $config = Feedamic::load(true);

    expect($config['feeds'])->toHaveCount(1)
        ->and($config['feeds'][0]['handle'])->toBe('content')
        ->and($config['feeds'][0]['title'])->toBe('Content')
        ->and($config['default_title'])->toBe(['heading', 'title']);
});

it('clears the feed cache and uses the new settings when saved', function () {
    createSuperUser();

    $key = ($this->cacheFeed)();

    $this->settings['feeds'][0]['title'] = 'Changed';

    $this->patchJson(cp_route('addons.settings.update', 'feedamic'), $this->settings)
        ->assertOk();

    expect(Cache::has($key))->toBeFalse()
        ->and(Feedamic::getConfig('/content/feed/atom', 'default')->title)->toBe('Changed');
});

it('does not clear the feed cache when another addon saves its settings', function () {
    $key = ($this->cacheFeed)();

    $settings = Mockery::mock(Settings::class);
    $settings->shouldReceive('addon')->andReturn(Addon::make('mitydigital/another'));

    AddonSettingsSaved::dispatch($settings);

    expect(Cache::has($key))->toBeTrue();
});
