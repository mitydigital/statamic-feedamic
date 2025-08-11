<?php

use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\AbstractFeedamicEntry;

it('uses the forwards calls trait', function () {
    expect(AbstractFeedamicEntry::class)
        ->toUse(ForwardsCalls::class);
});

it('is abstract', function () {
    expect(AbstractFeedamicEntry::class)->toBeAbstract();
});
