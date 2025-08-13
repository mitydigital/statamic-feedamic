<?php

namespace MityDigital\Feedamic;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Exceptions\BardContainsSetsException;
use MityDigital\Feedamic\Models\FeedamicConfig;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetCollection;
use Statamic\Entries\Entry;
use Statamic\Fields\Value;
use Statamic\Fieldtypes\Bard;
use Statamic\Modifiers\CoreModifiers;

abstract class AbstractFeedamicEntry
{
    use ForwardsCalls;

    protected static array $modifiers = [];

    protected string|Value $title;

    protected null|string|Value $summary;

    protected null|string|Value $content;

    protected null|Asset|Value $image;

    protected ?AbstractFeedamicAuthor $author;

    protected static bool $ignoreBardSets = false;

    public function __construct(public Entry $entry, protected FeedamicConfig $config) {}

    public static function ignoreBardSets(?bool $ignoreBardSets = null): bool
    {
        if ($ignoreBardSets !== null) {
            static::$ignoreBardSets = $ignoreBardSets;
        }

        return static::$ignoreBardSets;
    }

    public static function modify(string $field, Closure $modifier, ?Closure $when = null, ?array $feeds = null): void
    {
        static::$modifiers[] = [
            'feeds' => $feeds,
            'field' => $field,
            'modifier' => $modifier,
            'when' => $when,
        ];
    }

    public static function removeModifier(string $field): void
    {
        foreach (static::$modifiers as $idx => $modifier) {
            if ($modifier['field'] === $field) {
                unset(static::$modifiers[$idx]);
            }
        }
    }

    public function hasImage(): bool
    {
        return ! empty($this->image());
    }

    public function image(): null|Asset|Value
    {
        if (! $this->config->hasImage()) {
            $this->image = null;
        } elseif (! isset($this->image)) {
            $image = $this->processField(
                handle: 'image',
                value: $this->getMappingValue($this->config->getImageMappings())
            );

            $this->image = null;
            if ($image) {
                if ($image->value() instanceof Asset) {
                    $this->image = $image->value();
                } elseif ($image->value()?->get() instanceof AssetCollection) {
                    $this->image = $image->value()->get()->first();
                }
            }
        }

        return $this->image;
    }

    protected function getModifier(string $field, mixed $value): ?Closure
    {
        foreach (static::$modifiers as $modifier) {
            // get the field
            if ($modifier['field'] === $field) {
                if ($modifier['feeds'] === null || in_array($this->config->handle, $modifier['feeds'])) {
                    if ($when = $modifier['when']) {
                        if (! $when($value)) {
                            return null;
                        }
                    }

                    return $modifier['modifier'];
                }
            }
        }

        return null;
    }

    protected function processField(string $handle, mixed $value): mixed
    {
        if ($modifier = $this->getModifier($handle, $value)) {
            $value = $modifier($value);
        }

        // we may have bard here
        if ($value instanceof Value && $value->fieldtype() instanceof Bard) {
            $hasSets = collect($value->raw())
                ->first(fn (mixed $block) => is_array($block) && Arr::get($block, 'type', 'paragraph') === 'set');
            if (! static::$ignoreBardSets && $hasSets) {
                throw new BardContainsSetsException(__('feedamic::exceptions.bard_contains_sets', [
                    'handle' => $handle,
                ]));
            }

            $value = app(CoreModifiers::class)->bardHtml($value);
        }

        return $value;
    }

    protected function getMappingValue(array $map, mixed $default = null): mixed
    {
        foreach ($map as $handle) {
            if ($this->entry->has($handle)) {
                if ($value = $this->entry->augmentedValue($handle)) {
                    return $value;
                }
            }
        }

        return $default;
    }

    public function hasSummary(): bool
    {
        return ! empty($this->summary());
    }

    public function summary(): null|string|Value
    {
        if (! $this->config->hasSummary()) {
            $this->summary = null;
        } elseif (! isset($this->summary)) {
            $this->summary = $this->processField(
                handle: 'summary',
                value: $this->getMappingValue($this->config->getSummaryMappings())
            );
        }

        return $this->summary;
    }

    public function hasAuthor(): bool
    {
        return ! empty($this->author());
    }

    public function author(): ?AbstractFeedamicAuthor
    {
        if (! $this->config->hasAuthor()) {
            $this->author = null;
        } elseif (! isset($this->author)) {
            $this->author = null;

            $model = $this->config->author_model;
            if ($this->config->getAuthorType() === 'entry') {
                $author = $this->entry->augmentedValue($this->config->getAuthor())?->value();
                if ($author) {
                    $this->author = new $model($author, $this->config);
                }
            } else {
                $this->author = new $model($this->entry, $this->config);
            }
        }

        return $this->author;
    }

    public function hasContent(): bool
    {
        return ! empty($this->content());
    }

    public function content(): null|string|Value
    {
        if (! $this->config->hasContent()) {
            $this->content = null;
        } elseif (! isset($this->content)) {
            $this->content = $this->processField(
                handle: 'content',
                value: $this->getMappingValue($this->config->getContentMappings())
            );
        }

        return $this->content;
    }

    public function isHtml(mixed $value): bool
    {
        if ($value instanceof Value) {
            $value = $value->__toString();
        }

        return $value !== strip_tags($value);
    }

    public function title(): string|Value
    {
        if (! isset($this->title)) {
            $this->title = $this->processField(
                handle: 'title',
                value: $this->getMappingValue($this->config->getTitleMappings())
            );
        }

        return $this->title;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->entry, $name, $arguments);
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->entry->augmentedValue('updated_at')->raw();
    }

    public function url(): string
    {
        return $this->config->makeUrlAbsolute($this->entry->uri());
    }

    public function encode(string $html): string
    {
        return htmlspecialchars($html, ENT_XML1, 'UTF-8', false);
    }

    public function entry(): Entry
    {
        return $this->entry;
    }

    public function __get($name)
    {
        if (property_exists($this->entry, $name)) {
            return $this->entry->{$name};
        }

        throw new Exception("Property {$name} does not exist.");
    }

    public function __set($name, $value)
    {
        if (property_exists($this->entry, $name)) {
            $this->entry->{$name} = $value;

            return;
        }

        throw new Exception("Property {$name} does not exist.");
    }
}
