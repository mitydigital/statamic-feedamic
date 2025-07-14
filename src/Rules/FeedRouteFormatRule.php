<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Statamic\Facades\URL;

class FeedRouteFormatRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Str::startsWith($value, '/')) {
            $fail(__('feedamic::validation.feed_route_format_slash'));
        } elseif (Str::isUrl($value) || ! Str::isUrl(URL::makeAbsolute($value))) {
            $fail(__('feedamic::validation.feed_route_format'));
        }
    }
}
