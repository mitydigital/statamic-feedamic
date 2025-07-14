<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class AuthorNameRule implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    public function __construct(protected string $type = 'author_type') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = explode('.', $attribute);
        $field = array_pop($parts); // get rid of the field

        // what type are we looking at?
        if (count($parts) === 0) {
            $item = Arr::get($this->data, $field, null);
            $type = Arr::get($this->data, $this->type);
        } else {
            $item = Arr::get($this->data, implode('.', $parts), null);
            $type = Arr::get($item, $this->type);
        }

        if ($value === null) {
            $fail(__('feedamic::validation.author_name_null'));
        } elseif ($type === null) {
            $fail(__('feedamic::validation.author_name_missing_type'));
        } elseif ($type === 'entry') {
            // must be tokens wrapped in square brackets
            if (! preg_match_all('#\[(.*?)\]#', $value, $matches)) {
                $fail(__('feedamic::validation.author_name_entry'));
            }

            foreach (Arr::get($matches, 1, []) as $handle) {
                if (! $this->isHandle($handle)) {
                    $fail(__('statamic::validation.slug'));
                }
            }
        } elseif ($type === 'field') {
            // used the slug validation rule from Statamic core
            if (! $this->isHandle($value)) {
                $fail(__('statamic::validation.slug'));
            }
        }
    }

    protected function isHandle(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9]+(?:[-_]{0,1}[a-zA-Z0-9])*$/', $value);
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
