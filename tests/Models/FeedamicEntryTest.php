<?php

use MityDigital\Feedamic\AbstractFeedamicEntry;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicAuthor;
use MityDigital\Feedamic\Models\FeedamicConfig;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Assets\Asset;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;

beforeEach(function () {
    $this->defaults = collect([
        'default_title' => [
            'display_title',
            'title',
        ],
        'default_summary' => [
            'introduction',
        ],
        'default_content' => [
            'content',
        ],
        'default_image_enabled' => true,
        'default_image' => [
            'image',
        ],
        'default_image_width' => 1280,
        'default_image_height' => 720,
        'default_author_enabled' => true,
        'default_author_type' => 'entry',
        'default_author_field' => 'author_entry',
        'default_author_name' => '[name_first] [name_last]',
        'default_author_email' => 'email',
        'default_copyright' => 'Default Copyright Statement',
        'default_entry_model' => FeedamicEntry::class,
        'default_author_model' => FeedamicAuthor::class,
    ]);
    $this->config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'title_mode' => 'default',
            'summary_mode' => 'default',
            'content_mode' => 'default',
            'image_mode' => 'default',
            'author_mode' => 'default',
        ],
    ], $this->defaults);

    Collection::make('pages')->save();
    $this->author = Entry::make()
        ->blueprint('pages')
        ->collection('pages')
        ->slug('author')
        ->data([
            'title' => 'Author',
            'name_first' => 'Peter',
            'name_last' => 'Parker',
            'email' => 'peter@parker.com.au',
        ]);
    $this->author->save();

    Collection::make('blog')->save();
    $this->entry = Entry::make()
        ->blueprint('blog')
        ->collection('blog')
        ->slug('entry')
        ->data([
            'title' => 'Title',
            'display_title' => 'Display Title',
            'introduction' => 'Introduction',
            'content' => '<p>Hello, world!</p>',
            'image' => ['image-1.jpg'],
            'images' => ['image-array-1.jpg', 'image-array-2.jpg'],
            'author_entry' => $this->author->id,
        ]);
    $this->entry->saveQuietly();

    $this->feedamic = new FeedamicEntry(Entry::find($this->entry->id), $this->config);
});

it('extends the feedamic entry abstract', function () {
    expect(FeedamicEntry::class)
        ->toExtend(AbstractFeedamicEntry::class);
});

it('forwards calls', function () {
    expect($this->feedamic->slug())->toBe($this->entry->slug());
});

it('can have a custom modifier passed', function () {
    Feedamic::modify('title', function (AbstractFeedamicEntry $entry, ?Value $value) {
        return 'Modified!';
    });

    expect($this->feedamic->title())->toBe('Modified!');

    Feedamic::removeModifier('title');
});

it('returns the entry', function () {
    expect($this->feedamic->entry())
        ->toBeInstanceOf(\Statamic\Entries\Entry::class)
        ->toBe($this->entry);
});

it('gets the title from the entry', function () {
    expect($this->feedamic->title()->value())->toBe($this->entry->get('display_title'));
});

it('gets the summary from the entry', function () {
    expect($this->feedamic->hasSummary())->toBeTrue()
        ->and($this->feedamic->summary()?->value())->toBe($this->entry->get('introduction'));

    // disable in config
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'summary_mode' => 'disabled',
        ],
    ], $this->defaults);

    $this->feedamic = new FeedamicEntry($this->entry, $config);

    expect($this->feedamic->hasSummary())->toBeFalse()
        ->and($this->feedamic->summary())->toBeNull();
});

it('gets the content from the entry', function () {
    expect($this->feedamic->hasContent())->toBeTrue()
        ->and(trim($this->feedamic->content()))->toBe(trim($this->entry->get('content')));

    // disable in config
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'content_mode' => 'disabled',
        ],
    ], $this->defaults);

    $this->feedamic = new FeedamicEntry($this->entry, $config);

    expect($this->feedamic->hasContent())->toBeFalse()
        ->and($this->feedamic->content())->toBeNull();
});

it('gets the image from the entry', function () {
    expect($this->feedamic->hasImage())->toBeTrue()
        ->and($this->feedamic->image())
        ->toBeInstanceOf(Asset::class)
        ->and($this->feedamic->image()->path())->toBe('image-1.jpg');

    // from array
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'image_mode' => 'custom',
            'image' => ['images'],
        ],
    ], $this->defaults);

    $this->feedamic = new FeedamicEntry($this->entry, $config);

    expect($this->feedamic->hasImage())->toBeTrue()
        ->and($this->feedamic->image())
        ->toBeInstanceOf(Asset::class)
        ->and($this->feedamic->image()->path())->toBe('image-array-1.jpg');

    // disable in config
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'image_mode' => 'disabled',
        ],
    ], $this->defaults);

    $this->feedamic = new FeedamicEntry($this->entry, $config);

    expect($this->feedamic->hasImage())->toBeFalse()
        ->and($this->feedamic->image())->toBeNull();
});

it('gets the author from the entry', function () {
    expect($this->feedamic->hasAuthor())->toBeTrue()
        ->and($this->feedamic->author())->toBeInstanceOf(FeedamicAuthor::class)
        ->and($this->feedamic->author()->id())->toBe($this->author->id);

    // switch to field
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'author_mode' => 'default',
        ],
    ], $this->defaults->merge([
        'default_author_type' => 'field',
    ]));

    $this->feedamic = new FeedamicEntry($this->entry, $config);
    expect($this->feedamic->hasAuthor())->toBeTrue()
        ->and($this->feedamic->author())->toBeInstanceOf(FeedamicAuthor::class)
        ->and($this->feedamic->author()->id())->toBe($this->entry->id);

    // disable in config
    $config = new FeedamicConfig([
        'handle' => 'handle',
        'mappings' => [
            'author_mode' => 'disabled',
        ],
    ], $this->defaults);

    $this->feedamic = new FeedamicEntry($this->entry, $config);

    expect($this->feedamic->hasAuthor())->toBeFalse()
        ->and($this->feedamic->author())->toBeNull();
});
