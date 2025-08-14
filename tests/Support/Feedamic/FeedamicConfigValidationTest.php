<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MityDigital\Feedamic\Support\Feedamic;

it('has the correct validation for the field in the blueprint', function (string $handle, array $rules) {
    $blueprint = app(Feedamic::class)->blueprint();

    expect($blueprint->field($handle)->config()['validate'])
        ->toBeArray()
        ->toHaveCount(count($rules))
        ->toBe($rules);
})->with([
    'feeds' => [
        'feeds',
        [
            'required',
            'array',
            'min:1',
        ],
    ],
    'default title' => [
        'default_title',
        [
            'required',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default summary' => [
        'default_summary',
        [
            'nullable',
            'array',
            'min:0',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default image' => [
        'default_image',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'default image width' => [
        'default_image_width',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'integer',
            'min:1',
        ],
    ],
    'default image height' => [
        'default_image_height',
        [
            'exclude_unless:{this}.default_image_enabled,true',
            'integer',
            'min:1',
        ],
    ],
    'default author name' => [
        'default_author_name',
        [
            'exclude_unless:{this}.default_author_enabled,true',
            'required',
            'new \MityDigital\Feedamic\Rules\AuthorNameRule(default_author_type)',
        ],
    ],
    'default author field' => [
        'default_author_field',
        [
            'exclude_unless:{this}.default_author_enabled,true',
            'required',
        ],
    ],
    'default author email' => [
        'default_author_email',
        [
            'nullable',
            'new \Statamic\Rules\Slug',
        ],
    ],
    'default author model' => [
        'default_author_model',
        [
            'required',
        ],
    ],
    'default entry model' => [
        'default_entry_model',
        [
            'required',
        ],
    ],
]);

it('has the correct validation for the feeds array in the blueprint', function (string $handle, array $rules) {
    $blueprint = app(Feedamic::class)->blueprint();

    $fields = collect(Arr::get($blueprint->field('feeds')->config(), 'sets.types.sets.feed.fields', []))
        ->mapWithKeys(fn (array $field) => [$field['handle'] => $field['field']]);

    if (Str::contains($handle, '.')) {
        $outerField = Str::before($handle, '.');
        $innerField = Str::after($handle, '.');
        $field = Arr::get($fields, $outerField);
        $field = collect($field['fields'])
            ->mapWithKeys(fn (array $field) => [$field['handle'] => $field['field']])
            ->get($innerField);
    } else {
        $field = Arr::get($fields, $handle);
    }
    $validate = Arr::get($field, 'validate');

    expect($validate)
        ->toBeArray()
        ->toHaveCount(count($rules))
        ->toBe($rules);
})->with([
    'sites' => [
        'sites',
        [
            'required',
        ],
    ],
    'sites specific' => [
        'sites_specific',
        [
            'exclude_unless:{this}.sites,specific',
            'min:1',
        ],
    ],
    'title' => [
        'title',
        [
            'required',
        ],
    ],
    'handle' => [
        'handle',
        [
            'required',
            'new \MityDigital\Feedamic\Rules\HandleMustBeUniqueRule',
        ],
    ],
    'description' => [
        'description',
        [
            'required',
        ],
    ],
    'routes' => [
        'routes',
        [
            'new \MityDigital\Feedamic\Rules\RequiresAtLeastOneRouteRule',
        ],
    ],
    'routes atom' => [
        'routes.atom',
        [
            'nullable',
            'new \MityDigital\Feedamic\Rules\FeedRouteFormatRule',
        ],
    ],
    'routes rss' => [
        'routes.rss',
        [
            'nullable',
            'new \MityDigital\Feedamic\Rules\FeedRouteFormatRule',
        ],
    ],
    'collections' => [
        'collections',
        [
            'required',
            'new \MityDigital\Feedamic\Rules\CollectionsOrderedTheSameWayRule',
        ],
    ],
    'taxonomies terms' => [
        'taxonomies.terms',
        ['required', 'min:1'],
    ],
    'taxonomies logic' => [
        'taxonomies.logic',
        ['required'],
    ],
    'show limit' => [
        'show_limit',
        [
            'sometimes',
            'min:1',
            'integer',
        ],
    ],
    'mappings title' => [
        'mappings.title',
        [
            'exclude_unless:{this}.mappings.title_mode,custom',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings summary' => [
        'mappings.summary',
        [
            'exclude_unless:{this}.mappings.summary_mode,custom',
            'array',
            'min:0',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings image' => [
        'mappings.image',
        [
            'sometimes',
            'exclude_unless:{this}.mappings.image_mode,custom',
            'array',
            'min:1',
            'new \MityDigital\Feedamic\Rules\ListContainsHandlesRule',
        ],
    ],
    'mappings image width' => [
        'mappings.image_width',
        [
            'exclude_unless:{this}.mappings.image_dimensions_mode,custom',
            'integer',
            'min:1',
        ],
    ],
    'mappings image height' => [
        'mappings.image_height',
        [
            'exclude_unless:{this}.mappings.image_dimensions_mode,custom',
            'integer',
            'min:1',
        ],
    ],
    'mappings author type' => [
        'mappings.author_type',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
        ],
    ],
    'mappings author field' => [
        'mappings.author_field',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
            'required',
        ],
    ],
    'mappings author name' => [
        'mappings.author_name',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
            'new \MityDigital\Feedamic\Rules\AuthorNameRule',
        ],
    ],
    'mappings author email' => [
        'mappings.author_email',
        [
            'exclude_unless:{this}.mappings.author_mode,custom',
            'nullable',
            'new \Statamic\Rules\Slug',
        ],
    ],
    'copyright' => [
        'copyright',
        [
            'exclude_unless:{this}.copyright_mode,custom',
            'string',
        ],
    ],
    'author_model' => [
        'author_model',
        [
            'required',
        ],
    ],
    'entry_model' => [
        'entry_model',
        [
            'required',
        ],
    ],
    'alt_url' => [
        'alt_url',
        [
            'url',
        ],
    ],
]);
