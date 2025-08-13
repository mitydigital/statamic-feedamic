<?php

use MityDigital\Feedamic\Exceptions\CollectionMissingRouteException;
use Statamic\Facades\Collection;

beforeEach(function () {
    Collection::make('blog')
        ->dated(true)
        ->routes('/{mount}/{slug}')
        ->save();
});

it('returns 404 if no matching config found', function () {
    $this->get('/fake-feed')
        ->assertStatus(404);
});

it('does not return 404 for a matching feed', function () {
    $this->get('/feed/atom')
        ->assertStatus(200);
});

it('requires each collection have a route defined', function () {
    Collection::findByHandle('blog')->delete();
    Collection::make('blog')
        ->dated(true)
        ->save();

    $this->withoutExceptionHandling();
    $this->get('/feed/atom');
})->throws(CollectionMissingRouteException::class);
