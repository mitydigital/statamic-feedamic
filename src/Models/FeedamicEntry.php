<?php

namespace MityDigital\Feedamic\Models;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Entries\Entry;

class FeedamicEntry implements \MityDigital\Feedamic\Contracts\FeedamicEntry
{
    use ForwardsCalls;

    public function __construct(public Entry $entry) {}

    public function hasSummaryOrImage(): bool
    {
        return true;
    }

    public function title(bool $encode = true): string
    {
        return $this->entry->title;
    }

    public function summary(bool $encode = true): string
    {
        return 'Summary';
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->entry, $name, $arguments);
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

        throw new \Exception("Property {$name} does not exist.");
    }

    public function __set($name, $value)
    {
        if (property_exists($this->entry, $name)) {
            $this->entry->{$name} = $value;

            return;
        }

        throw new \Exception("Property {$name} does not exist.");
    }
}
