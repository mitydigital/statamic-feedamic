<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\Feedamic\Rules\ListContainsHandlesRule;

it('passes when all items in the list contain a value', function () {
    $data = [
        'list' => [
            'a',
            'b',
        ],
    ];
    $rule = new ListContainsHandlesRule;

    $validator = Validator::make($data, ['list' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('fails when a value in the list is not a valid handle', function (string $value, string $error) {
    $data = [
        'list' => [
            $value,
        ],
    ];
    $rule = new ListContainsHandlesRule;

    $validator = Validator::make($data, ['list' => [$rule]]);

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->get('list'))
        ->toHaveCount(1)
        ->toBe([
            __($error),
        ]);
})->with([
    'with spaces' => ['has spaces', 'statamic::validation.handle'],
    'starts with number' => ['0abc', 'statamic::validation.handle_starts_with_number'],
]);

it('fails when a value in the list is null or empty', function () {
    $data = [
        'list' => [
            'a',
            null,
        ],
    ];
    $rule = new ListContainsHandlesRule;

    $validator = Validator::make($data, ['list' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('list'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.list_contains_items'),
        ]);

    $data = [
        'list' => [
            'a',
            '',
        ],
    ];
    $rule = new ListContainsHandlesRule;

    $validator = Validator::make($data, ['list' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('list'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.list_contains_items'),
        ]);
});

it('fails when the value is not iterable', function () {
    $data = ['list' => 'string'];
    $rule = new ListContainsHandlesRule;

    $validator = Validator::make($data, ['list' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('list'))
        ->toHaveCount(1)
        ->toBe([
            __('feedamic::validation.list_contains_items_iterable'),
        ]);
});
