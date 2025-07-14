<?php

use MityDigital\Feedamic\Dictionaries\DatedCollectionsDictionary;
use Statamic\Dictionaries\BasicDictionary;
use Statamic\Facades\Collection;

it('extends the basic dictionary', function () {
    expect(DatedCollectionsDictionary::class)->toExtend(BasicDictionary::class);
});

it('correctly returns only dated collections', function () {
    Collection::make('dated')
        ->dated(true)
        ->save();

    Collection::make('undated')
        ->dated(false)
        ->save();

    $dictionary = new DatedCollectionsDictionary;

    expect($dictionary->options())->toBe([
        'dated' => 'Dated',
    ]);
});
