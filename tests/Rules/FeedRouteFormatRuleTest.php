<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\Feedamic\Rules\FeedRouteFormatRule;

it('passes when a valid relative url is provided with a slash', function () {
    $data = [
        'url' => '/feed/atom',
    ];
    $rule = new FeedRouteFormatRule;

    $validator = Validator::make($data, ['url' => [$rule]]);

    expect($validator->passes())->toBeTrue();
});

it('fails when it does not start with a slash', function () {
    $data = [
        'url' => 'feed/atom',
    ];
    $rule = new FeedRouteFormatRule;

    $validator = Validator::make($data, ['url' => [$rule]]);

    expect($validator->passes())->toBeFalse();
});

it('fails when a value cannot make a valid route', function (string $value) {
    $data = [
        'url' => $value,
    ];
    $rule = new FeedRouteFormatRule;

    $validator = Validator::make($data, ['url' => [$rule]]);

    expect($validator->passes())->toBeFalse()
        ->and($validator->errors()->get('url'))
        ->toContain(__('feedamic::validation.feed_route_format_slash'));
})->with([
    'invalid characters' => 'weird]/route',
    'space' => 'with a space',
    'another url' => 'https://www.google.com',
]);
