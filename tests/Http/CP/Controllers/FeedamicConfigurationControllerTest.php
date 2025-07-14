<?php

use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicEntry;

it('can show the feedamic configuration view', function () {
    createSuperUser();

    $this->get(route('statamic.cp.feedamic.config.show'))
        ->assertOk()
        ->assertViewIs('feedamic::cp.show');
});

it('requires the correct permission to view the configuration', function () {
    // not logged in
    $this->get(route('statamic.cp.feedamic.config.show'))
        ->assertRedirect(route('statamic.cp.login'));

    // logged in user, but cannot access cp
    $user = createCpUser();
    $this->get(route('statamic.cp.feedamic.config.show'))
        ->assertRedirect('/cp');

    // add role that grants access
    $role = createFeedamicRole();
    $user->explicitRoles([$role->handle()]);

    $this->get(route('statamic.cp.feedamic.config.show'))
        ->assertOk()
        ->assertViewIs('feedamic::cp.show');
});

it('requires the correct permission to update the configuration', function () {
    // not logged in
    $this->post(route('statamic.cp.feedamic.config.update'))
        ->assertRedirect(route('statamic.cp.login'));

    // logged in user, but cannot access cp
    $user = createCpUser();
    $this->post(route('statamic.cp.feedamic.config.update'))
        ->assertRedirect('/cp')
        ->assertSessionHasNoErrors();

    // add role that grants access
    $role = createFeedamicRole();
    $user->explicitRoles([$role->handle()]);

    $this->post(route('statamic.cp.feedamic.config.update'))
        ->assertRedirect()
        ->assertSessionHasErrors('feeds'); // expect "feeds" in the error array
});

it('updates the feedamic configuration', function () {
    $user = createCpUser();
    $role = createFeedamicRole();
    $user->explicitRoles([$role->handle()]);

    expect(file_exists(Feedamic::getPath()))->toBeFalse();

    // throws the expected validation errors
    $this->post(route('statamic.cp.feedamic.config.update'))
        ->assertRedirect()
        ->assertSessionHasErrors([
            'feeds',
            'default_title',
            'default_model',
        ]);

    // pass the expected fields
    $this->post(route('statamic.cp.feedamic.config.update'), [
        'feeds' => [
            [
                'type' => 'feed', // replicator
                'handle' => 'blog',
                'title' => 'Blog',
                'description' => 'My Blog Description',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/feed/atom',
                ],
                'collections' => ['blog'],
                'model' => FeedamicEntry::class,
            ],
        ],
        'default_title' => ['heading', 'title'],
        'default_model' => FeedamicEntry::class,
    ])
        ->assertOk();

    // file has been saved
    expect(file_exists(Feedamic::getPath()))->toBeTrue();

    $config = Feedamic::load();

    // make sure some values have been saved
    expect($config['feeds'])->toHaveCount(1)
        ->and($config['feeds'][0]['handle'])->toBe('blog')
        ->and($config['feeds'][0]['title'])->toBe('Blog')
        ->and($config['feeds'][0]['description'])->toBe('My Blog Description')
        ->and($config['default_title'])->toBe(['heading', 'title']);
});
