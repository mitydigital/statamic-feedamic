<?php

use Illuminate\Contracts\Validation\DataAwareRule;
use MityDigital\Feedamic\Rules\RequiresAtLeastOneRouteRule;

it('is a data aware rule', function () {
    expect(RequiresAtLeastOneRouteRule::class)->toImplement(DataAwareRule::class);
});

it('passes when there is at least one route defined', function (array $routes) {
    $data = [
        'routes' => $routes,
    ];
    $rule = new RequiresAtLeastOneRouteRule;

    $validator = Validator::make($data, ['routes' => [$rule]]);

    expect($validator->passes())->toBeTrue();
})->with([
    'both routes' => [
        [
            'atom' => '/atom',
            'rss' => '/rss',
        ],
    ],
    'one route' => [
        [
            'atom' => '/atom',
            'rss' => null,
        ],
    ],
]);

it('fails when there are no routes defined', function () {
    $data = [
        'routes' => [
            'atom' => null,
            'rss' => null,
        ],
    ];
    $rule = new RequiresAtLeastOneRouteRule;

    $validator = Validator::make($data, ['routes' => [$rule]]);

    expect($validator->passes())->toBeFalse();
});
