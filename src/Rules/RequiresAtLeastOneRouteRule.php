<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use MityDigital\Feedamic\Facades\Feedamic;

class RequiresAtLeastOneRouteRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $found = 0;
        foreach (Feedamic::getFeedTypes() as $feedType) {
            if (Arr::get($this->data, $attribute.'.'.$feedType)) {
                $found++;
            }
        }

        if (! $found) {
            $fail(__('feedamic::validation.requires_at_least_one_route'));
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
