<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class HandleMustBeUniqueRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $handles = collect($this->data['feeds'])
            ->groupBy('handle')
            ->map
            ->count();

        if ($handles->get($value, 0) > 1) {
            $fail(__('feedamic::validation.handle_must_be_unique'));
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
