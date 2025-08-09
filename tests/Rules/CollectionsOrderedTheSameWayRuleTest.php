<?php

use MityDigital\Feedamic\Rules\CollectionsOrderedTheSameWayRule;
use Statamic\Facades\Collection;

it('passes with a single dated collection', function () {
    Collection::make('blog')->dated(true)->saveQuietly();
    $data = [
        'collections' => [
            'blog',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('passes with a single non-dated collection', function () {
    Collection::make('blog')->dated(false)->saveQuietly();
    $data = [
        'collections' => [
            'blog',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('passes with multiple dated collections', function () {
    Collection::make('blog-a')->dated(true)->saveQuietly();
    Collection::make('blog-b')->dated(true)->saveQuietly();
    $data = [
        'collections' => [
            'blog-a',
            'blog-b',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('passes with multiple non-dated collections', function () {
    Collection::make('non-dated-a')->dated(false)->saveQuietly();
    Collection::make('non-dated-b')->dated(false)->saveQuietly();
    $data = [
        'collections' => [
            'non-dated-a',
            'non-dated-b',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('fails with mis-matching sort orders', function () {
    Collection::make('non-dated')->dated(false)->saveQuietly();
    Collection::make('blog')->dated(true)->saveQuietly();
    $data = [
        'collections' => [
            'blog',
            'non-dated',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeFalse();
});

it('fails with multiple non-dated collections with different sort orders', function () {
    Collection::make('non-dated-standard')->dated(false)->sortField('date')->saveQuietly();
    Collection::make('non-dated-custom')->dated(false)->sortField('title')->saveQuietly();

    $data = [
        'collections' => [
            'non-dated-standard',
            'non-dated-custom',
        ],
    ];
    $rule = new CollectionsOrderedTheSameWayRule;

    $validator = Validator::make($data, ['collections' => [$rule]]);

    expect($validator->passes())->toBeFalse();
});
