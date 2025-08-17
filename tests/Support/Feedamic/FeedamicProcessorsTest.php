<?php

use Illuminate\Support\Collection;
use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Exceptions\processorCallbackException;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\Entry;

it('can register a new processor', function () {
    Feedamic::processor(
        fieldHandle: 'handle',
        processor: function (AbstractFeedamicEntry $entry, $value) {},
        when: function (AbstractFeedamicEntry $entry, $value) {},
        feeds: ['feeds']);

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);

    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    $processor = $processors->first();
    expect($processor)
        ->toBeArray()->toHaveKeys(['handle', 'processor', 'when', 'feeds'])
        ->and($processor['handle'])->toBe('handle')
        ->and($processor['processor'])->toBeInstanceOf(Closure::class)
        ->and($processor['when'])->toBeInstanceOf(Closure::class)
        ->and($processor['feeds'])->toBeArray()
        ->toBe(['feeds']);
});

it('correctly assumes "feeds" is null', function () {
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);

    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($processors->first()['feeds'])->toBeNull();
});

it('can register multiple for a single field', function () {
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);

    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
});

it('can remove a processor', function () {
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);
    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    Feedamic::removeprocessor('handle');

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);
    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('removes all instances for a handle', function () {
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::processor('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::processor('other-handle', function (AbstractFeedamicEntry $entry, $value) {});

    Feedamic::removeprocessor('handle');

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $processors = getPrivateProperty($support, 'processors')->getValue($support);

    expect($processors)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($processors->pluck('handle'))->not()->toContain('handle');
});

it('throws the processor callback exception when the processor method signature is wrong', function () {
    $this->withoutExceptionHandling();
    Feedamic::processor('handle', function ($value) {});
})->throws(ProcessorCallbackException::class);

it('throws the processor callback exception when the "when" method signature is wrong', function () {
    $this->withoutExceptionHandling();
    Feedamic::processor(
        fieldHandle: 'handle',
        processor: function (AbstractFeedamicEntry $entry, $value) {},
        when: function ($value) {}
    );
})->throws(processorCallbackException::class);

it('can get a processor', function () {
    Feedamic::processor(
        fieldHandle: 'title',
        processor: function (AbstractFeedamicEntry $entry, $value) {
            return 'My processor';
        }
    );

    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $processor = Feedamic::getprocessor($entry, 'title', 'Hello, World');

    expect($processor)->toBeInstanceOf(Closure::class)
        ->and($processor($entry, 'Hello, World'))->toBe('My processor');
});

it('returns null when not found', function () {
    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $processor = Feedamic::getprocessor($entry, 'title', 'Hello, World');

    expect($processor)->toBeNull();
});

it('correctly executes the "when" check', function () {
    Feedamic::processor(
        fieldHandle: 'title',
        processor: function (AbstractFeedamicEntry $entry, $value) {},
        when: fn (AbstractFeedamicEntry $entry, $value) => config('feedamic.when_test', false)
    );

    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $processor = Feedamic::getprocessor($entry, 'title', 'Hello, World');
    expect($processor)->toBeNull();

    \Illuminate\Support\Facades\Config::set('feedamic.when_test', true);

    $processor = Feedamic::getprocessor($entry, 'title', 'Hello, World');
    expect($processor)->not()->toBeNull();
});
