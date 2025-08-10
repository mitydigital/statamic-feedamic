<?php

namespace MityDigital\Feedamic\Contracts;

use Statamic\Assets\Asset;
use Statamic\Fields\Value;

interface FeedamicEntry
{
    public function hasImage(): bool;

    public function hasSummary(): bool;

    public function title(): string|Value;

    public function summary(): null|string|Value;

    public function content(): null|string|Value;

    public function image(): null|Asset|Value;
}
