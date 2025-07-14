<?php

use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Contracts\FeedamicEntry;

it('implements the feedamic entry interface', function () {
    expect(\MityDigital\Feedamic\Models\FeedamicEntry::class)
        ->toImplement(FeedamicEntry::class);
});

it('uses the forwards calls trait', function () {
    expect(\MityDigital\Feedamic\Models\FeedamicEntry::class)
        ->toUse(ForwardsCalls::class);
});
