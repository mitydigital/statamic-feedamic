<?php

namespace MityDigital\Feedamic\Contracts;

interface FeedamicEntry {

    public function __construct(array $attributes);
    public function hasSummaryOrImage():bool;
    public function title(bool $encode = true): string;
    public function summary(bool $encode = true): string;
}
