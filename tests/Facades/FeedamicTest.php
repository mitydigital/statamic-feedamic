<?php

use Illuminate\Support\Facades\Facade;
use MityDigital\Feedamic\Facades\Feedamic;

beforeEach(function () {
    // You may want to clear any previously resolved instances
    Facade::clearResolvedInstances();
});

it('calls the underlying service via the facade', function () {
    $this->partialMock(\MityDigital\Feedamic\Support\Feedamic::class, function ($mock) {
        $mock->shouldReceive('getFeedTypes')
            ->once()
            ->andReturn(['atom', 'rss']);
    });

    $result = Feedamic::getFeedTypes();

    expect($result)->toBe(['atom', 'rss']);
});
