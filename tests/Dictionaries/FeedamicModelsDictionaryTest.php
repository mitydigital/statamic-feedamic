<?php

use App\Models\User;
use MityDigital\Feedamic\Dictionaries\FeedamicEntryModelsDictionary;
use Statamic\Dictionaries\BasicDictionary;

it('extends the basic dictionary', function () {
    expect(FeedamicEntryModelsDictionary::class)->toExtend(BasicDictionary::class);
});

it('correctly returns only models that are for feedamic', function () {
    $dictionary = new FeedamicEntryModelsDictionary;

    // confirm we have a "user" model
    expect(new User)->not()->toBeNull();

    expect($dictionary->options())
        ->toBe([
            'MityDigital\Feedamic\Models\FeedEntry' => 'MityDigital\Feedamic\Models\FeedEntry',
            'App\Models\MyCustomFeedamicEntry' => 'App\Models\MyCustomFeedamicEntry',
        ])
        ->not()
        ->toContain('App\Models\User');
});
