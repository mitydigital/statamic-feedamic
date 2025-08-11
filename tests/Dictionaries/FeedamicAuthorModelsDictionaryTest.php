<?php

use App\Models\User;
use MityDigital\Feedamic\Dictionaries\FeedamicAuthorModelsDictionary;
use Statamic\Dictionaries\BasicDictionary;

it('extends the basic dictionary', function () {
    expect(FeedamicAuthorModelsDictionary::class)->toExtend(BasicDictionary::class);
});

it('correctly returns only models that are for feedamic', function () {
    $dictionary = new FeedamicAuthorModelsDictionary;

    // confirm we have a "user" model
    expect(new User)->not()->toBeNull();

    expect($dictionary->options())
        ->toBe([
            'MityDigital\Feedamic\Models\FeedamicAuthor' => 'MityDigital\Feedamic\Models\FeedamicAuthor',
            'App\Models\MyCustomFeedamicAuthor' => 'App\Models\MyCustomFeedamicAuthor',
            'App\Models\Nested\MyCustomFeedamicAuthor' => 'App\Models\Nested\MyCustomFeedamicAuthor',
        ])
        ->not()
        ->toContain('App\Models\User');
});
