<?php

use MityDigital\Feedamic\Contracts\FeedamicEntry;

it('defines the expected methods', function () {
    $interface = new ReflectionClass(FeedamicEntry::class);
    $methods = collect($interface->getMethods())->pluck('name');

    expect($methods)->toContain('title')
        ->and($methods)->toContain('summary')
        ->and($methods)->toContain('content')
        ->and($methods)->toContain('hasImage')
        ->and($methods)->toContain('hasSummary');
});
