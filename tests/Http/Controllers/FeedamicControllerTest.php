<?php

use Statamic\Facades\Collection;

beforeEach(function () {
    Collection::make('blog')->dated(true)->save();
});

it('returns 404 if no matching config found', function () {
    $this->get('/fake-feed')
        ->assertStatus(404);
});

it('does not return 404 for a matching feed', function () {
    $this->get('/feed/atom')
        ->assertStatus(200);
});
