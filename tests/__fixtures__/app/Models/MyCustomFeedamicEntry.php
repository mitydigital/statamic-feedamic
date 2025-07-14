<?php

namespace App\Models;

use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Contracts\FeedamicEntry;

class MyCustomFeedamicEntry implements FeedamicEntry
{
    use ForwardsCalls;

    public function __construct(public $entry) {}

    public function hasSummaryOrImage(): bool
    {
        return true;
    }

    public function title(bool $encode = true): string
    {
        return 'Title';
    }

    public function summary(bool $encode = true): string
    {
        return 'Summary';
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->entry, $name, $arguments);
    }
}
