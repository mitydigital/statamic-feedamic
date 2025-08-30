<?php

namespace MityDigital\Feedamic\Abstracts;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Exceptions\BardContainsSetsException;
use MityDigital\Feedamic\Facades\Feedamic;
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

    protected static bool $ignoreBardSets = false;

    protected string|Value $title;

    protected null|string|Value $summary;

    protected null|string|Value $content;

    protected null|Asset|Value|string $image;

    protected ?AbstractFeedamicAuthor $author;

    public function __construct(public Entry $entry, protected FeedamicConfig $config) {}

    public static function ignoreBardSets(?bool $ignoreBardSets = null): bool
    {
        if ($ignoreBardSets !== null) {
            static::$ignoreBardSets = $ignoreBardSets;
        }

        return static::$ignoreBardSets;
    }

    public function hasImage(): bool
    {
        return ! empty($this->image());
    }

    public function image(): null|Asset|Value|string
    {
        if (! $this->config->hasImage()) {
            $this->image = null;
        } elseif (! isset($this->image)) {
            $image = $this->processField($this->config->getImageMappings(), 'image');

            $this->image = null;
            if ($image) {
                if (is_string($image)) {
                    $this->image = $image;
                } elseif ($image->value() instanceof Asset) {
                    $this->image = $image->value();
                } elseif ($image->value()?->get() instanceof AssetCollection) {
                    $this->image = $image->value()->get()->first();
                }
            }
        }

        return $this->image;
    }

    protected function processField(array $map, string $property): mixed
    {
        // set our values
        $fieldHandle = null;
        $fieldValue = null;

        // look at the map
        foreach ($map as $handle) {
            $fieldHandle = $handle; // always set
            if ($this->entry->has($handle)) {
                if ($value = $this->entry->augmentedValue($handle)) {
                    $fieldValue = $value;
                    break;
                } elseif ($this->entry->computedKeys()->contains($handle)) {
                    if ($value = $this->entry->getComputed($handle)) {
                        $fieldValue = $value;
                        break;
                    }
                }
            }
        }

        if (! $fieldHandle) {
            return null;
        }

        // get a processor
        if ($processor = Feedamic::getProcessor($this, $fieldHandle, $fieldValue)) {
            $fieldValue = $processor($this, $fieldValue);
        }

        // we may have bard here
        if ($fieldValue instanceof Value && $fieldValue->fieldtype() instanceof Bard) {
            $hasSets = collect($fieldValue->raw())
                ->first(fn (mixed $block) => is_array($block) && Arr::get($block, 'type', 'paragraph') === 'set');
            if (! static::$ignoreBardSets && $hasSets) {
                throw new BardContainsSetsException(__('feedamic::exceptions.bard_contains_sets', [
                    'handle' => $fieldHandle,
                ]));
            }

            $fieldValue = app(CoreModifiers::class)->fullUrls(app(CoreModifiers::class)->bardHtml($fieldValue));
        } elseif (is_string($fieldValue)) {
            $fieldValue = app(CoreModifiers::class)->fullUrls($fieldValue);
        }

        // get a modifier
        if ($modifier = Feedamic::getModifier($this, $property, $fieldValue)) {
            $fieldValue = $modifier($this, $fieldValue);
        }

        return $fieldValue;
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
            $this->summary = $this->processField($this->config->getSummaryMappings(), 'summary');
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
            $this->content = $this->processField($this->config->getContentMappings(), 'content');
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
            $this->title = $this->processField($this->config->getTitleMappings(), 'title');
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

    public function hasUrl(): bool
    {
        return $this->entry->uri() ? true : false;
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

    public function config(): FeedamicConfig
    {
        return $this->config;
    }
}
