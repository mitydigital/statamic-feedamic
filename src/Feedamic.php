<?php

namespace MityDigital\Feedamic;

use Statamic\Facades\Addon;

class Feedamic
{
    public static function version(): string
    {
        return Addon::get('mitydigital/feedamic')->version();
    }
}