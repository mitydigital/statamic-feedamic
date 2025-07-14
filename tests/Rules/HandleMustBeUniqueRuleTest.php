<?php

use MityDigital\Feedamic\Rules\HandleMustBeUniqueRule;

it('passes when a single feed exists', function () {
    $data = [
        'feeds' => [
            [
                'handle' => 'blog',
            ],
        ],
    ];
    $rule = new HandleMustBeUniqueRule;

    $validator = Validator::make($data, ['feeds.*.handle' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('passes when a multiple feeds with different handles exist', function () {
    $data = [
        'feeds' => [
            [
                'handle' => 'blog',
            ],
            [
                'handle' => 'news',
            ],
        ],
    ];
    $rule = new HandleMustBeUniqueRule;

    $validator = Validator::make($data, ['feeds.*.handle' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('fails when multiple feeds exist with the same handle', function () {
    $data = [
        'feeds' => [
            [
                'handle' => 'blog',
            ],
            [
                'handle' => 'blog',
            ],
        ],
    ];
    $rule = new HandleMustBeUniqueRule;

    $validator = Validator::make($data, ['feeds.*.handle' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('feeds.0.handle'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.handle_must_be_unique'),
        ])
        ->and($validator->errors()->get('feeds.1.handle'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.handle_must_be_unique'),
        ]);
});

it('fails only the erroneous fields when multiple feeds exist with some having the same handle', function () {
    $data = [
        'feeds' => [
            [
                'handle' => 'blog',
            ],
            [
                'handle' => 'news',
            ],
            [
                'handle' => 'blog',
            ],
        ],
    ];
    $rule = new HandleMustBeUniqueRule;

    $validator = Validator::make($data, ['feeds.*.handle' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('feeds.0.handle'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.handle_must_be_unique'),
        ])
        ->and($validator->errors()->get('feeds.1.handle'))
        ->toHaveCount(0)
        ->and($validator->errors()->get('feeds.2.handle'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.handle_must_be_unique'),
        ]);
});
