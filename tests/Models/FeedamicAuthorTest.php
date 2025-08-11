<?php

use MityDigital\Feedamic\AbstractFeedamicAuthor;
use MityDigital\Feedamic\Models\FeedamicAuthor;
use MityDigital\Feedamic\Models\FeedamicConfig;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

it('extends the feedamic author abstract', function () {
    expect(FeedamicAuthor::class)
        ->toExtend(AbstractFeedamicAuthor::class);
});

it('returns null as the name when author is disabled', function () {
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'disabled',
        ],
    ], collect());

    $author = new FeedamicAuthor(createSuperUser(), $config);

    expect($author->name())->toBeNull();
});

it('gets the name from the resource when configured as field', function () {
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'field',
            'author_name' => 'email',
        ],
    ], collect());

    $user = createSuperUser();
    $author = new FeedamicAuthor($user, $config);

    expect($author->name())->toBe($user->email());

    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'field',
            'author_name' => 'title',
        ],
    ], collect());

    $user = createSuperUser();
    $author = new FeedamicAuthor($user, $config);

    expect($author->name())->toBe($user->title);

    // as an entry
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'field',
            'author_name' => 'title',
        ],
    ], collect());

    Collection::make('blog')->save();
    $entry = Entry::make()
        ->blueprint('blog')
        ->collection('blog')
        ->slug('entry')
        ->data([
            'title' => 'Title',
            'name_first' => 'Peter',
            'name_last' => 'Parker',
            'email' => 'peter@parker.com.au',
        ]);
    $entry->save();

    $author = new FeedamicAuthor($entry, $config);

    expect($author->name())->toBe($entry->title);
});

it('gets the name from the resource when configured as entry', function () {
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'entry',
            'author_name' => '[email] : [title]',
        ],
    ], collect());

    $user = createSuperUser();
    $author = new FeedamicAuthor($user, $config);

    expect($author->name())->toBe(sprintf('%s : %s', $user->email, $user->title));

    // as an entry
    Collection::make('blog')->save();
    $entry = Entry::make()
        ->blueprint('blog')
        ->collection('blog')
        ->slug('entry')
        ->data([
            'title' => 'Title',
            'name_first' => 'Peter',
            'name_last' => 'Parker',
            'email' => 'peter@parker.com.au',
        ]);
    $entry->save();

    $author = new FeedamicAuthor($entry, $config);

    expect($author->name())->toBe(sprintf('%s : %s', $entry->get('email'), $entry->title));
});

it('gets the email from the resource when configured as field', function () {
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'field',
            'author_email' => 'title',
        ],
    ], collect());

    $user = createSuperUser();
    $author = new FeedamicAuthor($user, $config);

    expect($author->email())->toBe($user->title);

    // as an entry
    Collection::make('blog')->save();
    $entry = Entry::make()
        ->blueprint('blog')
        ->collection('blog')
        ->slug('entry')
        ->data([
            'title' => 'Title',
            'name_first' => 'Peter',
            'name_last' => 'Parker',
            'email' => 'peter@parker.com.au',
        ]);
    $entry->save();

    $author = new FeedamicAuthor($entry, $config);

    expect($author->email())->toBe($entry->title);
});

it('gets the email from the resource when configured as entry', function () {
    $config = new FeedamicConfig([
        'handle' => 'author-test',
        'mappings' => [
            'author_mode' => 'custom',
            'author_type' => 'entry',
            'author_email' => 'email',
        ],
    ], collect());

    $user = createSuperUser();
    $author = new FeedamicAuthor($user, $config);

    expect($author->email())->toBe($user->email);

    // as an entry
    Collection::make('blog')->save();
    $entry = Entry::make()
        ->blueprint('blog')
        ->collection('blog')
        ->slug('entry')
        ->data([
            'title' => 'Title',
            'name_first' => 'Peter',
            'name_last' => 'Parker',
            'email' => 'peter@parker.com.au',
        ]);
    $entry->save();

    $author = new FeedamicAuthor($entry, $config);

    expect($author->email())->toBe($entry->get('email'));
});
