<?php

use Illuminate\Support\Collection;
use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Exceptions\ModifierCallbackException;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\Entry;

it('can register a new modifier', function () {
    Feedamic::modify(
        fieldHandle: 'handle',
        modifier: function (AbstractFeedamicEntry $entry, $value) {},
        when: function (AbstractFeedamicEntry $entry, $value) {},
        feeds: ['feeds']);

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);

    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    $modifier = $modifiers->first();
    expect($modifier)
        ->toBeArray()->toHaveKeys(['handle', 'modifier', 'when', 'feeds'])
        ->and($modifier['handle'])->toBe('handle')
        ->and($modifier['modifier'])->toBeInstanceOf(Closure::class)
        ->and($modifier['when'])->toBeInstanceOf(Closure::class)
        ->and($modifier['feeds'])->toBeArray()
        ->toBe(['feeds']);
});

it('correctly assumes "feeds" is null', function () {
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);

    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($modifiers->first()['feeds'])->toBeNull();
});

it('can register multiple for a single field', function () {
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);

    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2);
});

it('can remove a modifier', function () {
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);
    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    Feedamic::removeModifier('handle');

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);
    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(0);
});

it('removes all instances for a handle', function () {
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::modify('handle', function (AbstractFeedamicEntry $entry, $value) {});
    Feedamic::modify('other-handle', function (AbstractFeedamicEntry $entry, $value) {});

    Feedamic::removeModifier('handle');

    $support = app(\MityDigital\Feedamic\Support\Feedamic::class);
    $modifiers = getPrivateProperty($support, 'modifiers')->getValue($support);

    expect($modifiers)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($modifiers->pluck('handle'))->not()->toContain('handle');
});

it('throws the modifier callback exception when the modifier method signature is wrong', function () {
    $this->withoutExceptionHandling();
    Feedamic::modify('handle', function ($value) {});
})->throws(ModifierCallbackException::class);

it('throws the modifier callback exception when the "when" method signature is wrong', function () {
    $this->withoutExceptionHandling();
    Feedamic::modify(
        fieldHandle: 'handle',
        modifier: function (AbstractFeedamicEntry $entry, $value) {},
        when: function ($value) {}
    );
})->throws(ModifierCallbackException::class);

it('can get a modifier', function () {
    Feedamic::modify(
        fieldHandle: 'title',
        modifier: function (AbstractFeedamicEntry $entry, $value) {
            return 'My Modifier';
        }
    );

    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $modifier = Feedamic::getModifier($entry, 'title', 'Hello, World');

    expect($modifier)->toBeInstanceOf(Closure::class)
        ->and($modifier($entry, 'Hello, World'))->toBe('My Modifier');
});

it('returns null when not found', function () {
    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $modifier = Feedamic::getModifier($entry, 'title', 'Hello, World');

    expect($modifier)->toBeNull();
});

it('correctly executes the "when" check', function () {
    Feedamic::modify(
        fieldHandle: 'title',
        modifier: function (AbstractFeedamicEntry $entry, $value) {},
        when: fn (AbstractFeedamicEntry $entry, $value) => config('feedamic.when_test', false)
    );

    $config = Feedamic::getConfig('/feed/atom', 'default');
    $entry = new FeedamicEntry(Entry::all()->first(), $config);

    $modifier = Feedamic::getModifier($entry, 'title', 'Hello, World');
    expect($modifier)->toBeNull();

    \Illuminate\Support\Facades\Config::set('feedamic.when_test', true);

    $modifier = Feedamic::getModifier($entry, 'title', 'Hello, World');
    expect($modifier)->not()->toBeNull();
});
