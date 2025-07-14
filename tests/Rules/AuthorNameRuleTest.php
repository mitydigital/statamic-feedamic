<?php

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Validator;
use MityDigital\Feedamic\Rules\AuthorNameRule;

it('is a data aware rule', function () {
    expect(AuthorNameRule::class)->toImplement(DataAwareRule::class);
});

it('passes for entry when there is an "entry" type', function (array $data, array $rules, bool $pass, ?array $errors) {
    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeBool()->toBe($pass);

    if ($errors) {
        expect($validator->errors()->get('author_name'))->toBe($errors);
    } else {
        expect($validator->errors()->get('author_name'))->toHaveCount(0);
    }
})->with([
    'array with default type' => [
        [
            'mappings' => [
                [
                    'author_type' => 'entry',
                    'author_name' => '[name]',
                ],
            ],
        ],
        ['mappings.*.author_name' => [new AuthorNameRule]],
        true,
        null,
    ],

    'array with custom type' => [
        [
            'mappings' => [
                [
                    'custom_type' => 'entry',
                    'author_name' => '[name]',
                ],
            ],
        ],
        ['mappings.*.author_name' => [new AuthorNameRule('custom_type')]],
        true,
        null,
    ],

    'flat with default type' => [
        [
            'author_type' => 'entry',
            'author_name' => '[name]',
        ],
        ['author_name' => [new AuthorNameRule]],
        true,
        null,
    ],

    'flat with custom type' => [
        [
            'custom_type' => 'entry',
            'author_name' => '[name]',
        ],
        ['author_name' => [new AuthorNameRule('custom_type')]],
        true,
        null,
    ],

]);

it('fails when value is null', function (array $data, array $rules, string $location) {
    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get($location))->toBe([
            __('feedamic::validation.author_name_null'),
        ]);
})->with([
    'array with no value' => [
        [
            'mappings' => [
                [
                    'author_name' => null,
                ],
            ],
        ],
        ['mappings.*.author_name' => [new AuthorNameRule]],
        'mappings.0.author_name',
    ],

    'flat with no value' => [
        [
            'author_name' => null,
        ],
        ['author_name' => [new AuthorNameRule]],
        'author_name',
    ],
]);

it('fails when there is no type', function (string $field) {
    $data = [
        'not_the_field' => 'entry',
        'author_name' => '[name]',
    ];

    $validator = Validator::make($data, ['author_name' => [new AuthorNameRule($field)]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('author_name'))->toBe([
            __('feedamic::validation.author_name_missing_type'),
        ]);
})->with([
    ['author_type'],
    ['type'],
]);

it('correctly validates square bracketed tokens when doing "entry" type', function (string $name, bool $pass) {
    $data = [
        'author_type' => 'entry',
        'author_name' => $name,
    ];

    $validator = Validator::make($data, ['author_name' => [new AuthorNameRule]]);

    expect($validator->passes())->toBeBool()->toBe($pass);

    if (! $pass) {
        expect($validator->errors()->get('author_name'))->toHaveCount(1);
    } else {
        expect($validator->errors()->get('author_name'))->toHaveCount(0);
    }
})->with([
    'valid token' => ['[name_first]', true],
    'valid tokens' => ['[name_first] [name_last]', true],
    'broken token' => ['[name_first', false],
    'broken tokens' => ['[name_first [name_last]', false],
    'mismatched brackets' => ['][name_first [name_last]', false],
    'empty token' => ['[]', false],
]);

it('passes for entry when there is an "field" type', function (array $data, array $rules, bool $pass, ?array $errors) {
    $validator = Validator::make($data, $rules);

    expect($validator->passes())->toBeBool()->toBe($pass);

    if ($errors) {
        expect($validator->errors()->get('author_name'))->toBe($errors);
    } else {
        expect($validator->errors()->get('author_name'))->toHaveCount(0);
    }
})->with([
    'array with default type' => [
        [
            'mappings' => [
                [
                    'author_type' => 'field',
                    'author_name' => 'name',
                ],
            ],
        ],
        ['mappings.*.author_name' => [new AuthorNameRule]],
        true,
        null,
    ],

    'array with custom type' => [
        [
            'mappings' => [
                [
                    'custom_type' => 'field',
                    'author_name' => 'name',
                ],
            ],
        ],
        ['mappings.*.author_name' => [new AuthorNameRule('custom_type')]],
        true,
        null,
    ],

    'flat with default type' => [
        [
            'author_type' => 'field',
            'author_name' => 'name',
        ],
        ['author_name' => [new AuthorNameRule]],
        true,
        null,
    ],

    'flat with custom type' => [
        [
            'custom_type' => 'field',
            'author_name' => 'name',
        ],
        ['author_name' => [new AuthorNameRule('custom_type')]],
        true,
        null,
    ],

]);

it('correctly validates with a handle (slug) when doing "field" type', function (string $name, bool $pass) {
    $data = [
        'author_type' => 'field',
        'author_name' => $name,
    ];

    $validator = Validator::make($data, ['author_name' => [new AuthorNameRule]]);

    expect($validator->passes())->toBeBool()->toBe($pass);

    if (! $pass) {
        expect($validator->errors()->get('author_name'))->toHaveCount(1);
    } else {
        expect($validator->errors()->get('author_name'))->toHaveCount(0);
    }
})->with([
    'valid' => ['name', true],
    'invalid with space' => ['name first', false],
    'invalid characters' => ['[name_first', false],
]);
