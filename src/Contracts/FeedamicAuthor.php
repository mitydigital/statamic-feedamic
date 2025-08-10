<?php

namespace MityDigital\Feedamic\Contracts;

use Statamic\Fields\Value;

interface FeedamicAuthor
{
    public function name(): string|Value;

    public function email(): null|string|Value;
}
