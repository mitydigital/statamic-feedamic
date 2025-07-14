<?php

use MityDigital\Feedamic\Dictionaries\FeedamicScopesDictionary;
use Statamic\Dictionaries\BasicDictionary;

it('extends the basic dictionary', function () {
    expect(FeedamicScopesDictionary::class)->toExtend(BasicDictionary::class);
});

it('correctly returns only scopes', function () {
    $dictionary = new FeedamicScopesDictionary;

    expect($dictionary->options())->toBe([
        'App\Scopes\MyQueryScope' => 'App\Scopes\MyQueryScope',
    ]);
});
