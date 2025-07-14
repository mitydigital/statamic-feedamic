<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Rules\Handle;

class ListContainsHandlesRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_iterable($value)) {
            $fail(__('feedamic::validation.list_contains_items_iterable'))->translate();
        } else {
            foreach ($value as $item) {
                if (! $item) {
                    $fail(__('feedamic::validation.list_contains_items'))->translate();
                } else {
                    (new Handle)->validate($attribute, $item, $fail);
                }
            }
        }
    }
}
