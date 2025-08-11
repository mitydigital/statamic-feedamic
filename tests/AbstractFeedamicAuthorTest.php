<?php

use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\AbstractFeedamicAuthor;

it('uses the forwards calls trait', function () {
    expect(AbstractFeedamicAuthor::class)
        ->toUse(ForwardsCalls::class);
});

it('is abstract', function () {
    expect(AbstractFeedamicAuthor::class)->toBeAbstract();
});
