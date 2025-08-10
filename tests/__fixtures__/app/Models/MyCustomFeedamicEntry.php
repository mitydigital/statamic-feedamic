<?php

namespace App\Models;

use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Contracts\FeedamicEntry;
use Statamic\Assets\Asset;
use Statamic\Fields\Value;

class MyCustomFeedamicEntry implements FeedamicEntry
{
    use ForwardsCalls;

    public function __construct(public $entry) {}

    public function hasSummary(): bool
    {
        if ($this->summary || $this->image) {
            return true;
        }

        return false;
    }

    public function hasImage(): bool
    {
        if ($this->summary || $this->image) {
            return true;
        }

        return false;
    }

    public function image(): ?Asset
    {
        return null;
    }

    public function title(): string|Value
    {
        return null;
    }

    public function content(): null|string|Value
    {
        return null;
    }

    public function summary(): null|string|Value
    {
        return null;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->entry, $name, $arguments);
    }
}
