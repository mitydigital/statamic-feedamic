<?php

use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Models\FeedamicEntry;
use Statamic\Facades\YAML;

beforeEach(function () {
    $default = collect(YAML::file(resource_path('addons/feedamic.yaml'))->parse());

    FeedamicEntry::ignoreBardSets(true);

    Feedamic::save(array_merge($default->toArray(), [
        'feeds' => [
            [
                'handle' => 'render-html',
                'title' => 'HTML Render Test',
                'description' => 'Testing the HTML rendering',
                'sites' => 'all',
                'routes' => [
                    'atom' => '/render-html/feed/atom',
                    'rss' => '/render-html/feed',
                ],
                'collections' => [
                    'feed_content_types',
                ],
                'mappings' => [
                    'title_mode' => 'default',
                    'summary_mode' => 'default',
                    'image_mode' => 'default',
                    'author_mode' => 'default',
                    'content_mode' => 'default',
                ],
            ],
        ],
        'default_summary' => ['content'],
    ]));
    Feedamic::load(true);

    // parse the feed, and get the first matching node in the newest entry
    $this->node = function (string $route, string $path): DOMElement {
        $document = new DOMDocument;
        $document->loadXML(Feedamic::render(Feedamic::getConfig($route, 'default'), $route));

        $entry = $document->getElementsByTagName($route === '/render-html/feed' ? 'item' : 'entry')->item(0);

        return $entry->getElementsByTagName($path)->item(0);
    };

    $this->modify = fn (string $property, string $return) => Feedamic::modify(
        $property,
        fn (AbstractFeedamicEntry $entry, mixed $value) => $return
    );
});

afterEach(function () {
    FeedamicEntry::ignoreBardSets(false);
});

dataset('values', [
    'html' => ['<p>It&#039;s <em>HTML</em></p>', '<p>It&#039;s <em>HTML</em></p>'],
    'entities without markup' => ['couldn&#039;t &amp; caf&eacute;', 'couldn&#039;t &amp; caf&eacute;'],
    'plain text' => ["R&D couldn't, 5 < 6", 'R&amp;D couldn&apos;t, 5 &lt; 6'],
    'plain text with line breaks' => ["One\nTwo\n", "One<br>\nTwo"],
]);

it('outputs the summary as html in atom', function (string $value, string $expected) {
    ($this->modify)('summary', $value);

    $summary = ($this->node)('/render-html/feed/atom', 'summary');

    expect($summary->getAttribute('type'))->toBe('html')
        ->and($summary->textContent)->toBe($expected);
})->with('values');

it('outputs the content as html in atom', function (string $value, string $expected) {
    ($this->modify)('content', $value);

    $content = ($this->node)('/render-html/feed/atom', 'content');

    expect($content->getAttribute('type'))->toBe('html')
        ->and($content->textContent)->toBe($expected);
})->with('values');

it('outputs the summary as html in rss', function (string $value, string $expected) {
    ($this->modify)('summary', $value);

    expect(($this->node)('/render-html/feed', 'description')->textContent)->toBe($expected);
})->with('values');

it('outputs the summary alongside an image', function (string $route, string $path) {
    ($this->modify)('title', 'A "quoted" <strong>title</strong> &amp; more');
    ($this->modify)('summary', '<p>First</p><p>Second</p>');
    ($this->modify)('image', 'https://feedamic.test/image.jpg');

    expect(($this->node)($route, $path)->textContent)
        ->toContain('alt="A &quot;quoted&quot; title &amp; more"')
        ->toContain('<div><p>First</p><p>Second</p></div>');
})->with([
    'atom' => ['/render-html/feed/atom', 'summary'],
    'rss' => ['/render-html/feed', 'description'],
]);

it('outputs a plain text summary alongside an image', function (string $route, string $path) {
    ($this->modify)('summary', 'R&D, 5 < 6');
    ($this->modify)('image', 'https://feedamic.test/image.jpg');

    expect(($this->node)($route, $path)->textContent)
        ->toContain('<div>R&amp;D, 5 &lt; 6</div>');
})->with([
    'atom' => ['/render-html/feed/atom', 'summary'],
    'rss' => ['/render-html/feed', 'description'],
]);
