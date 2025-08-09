<?php

namespace MityDigital\Feedamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Collection;

class CollectionsOrderedTheSameWayRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sortField = null;
        foreach ($value as $handle) {
            $collection = Collection::find($handle);

            if (! $collection) {
                $fail(__('feedamic::validation.collections_ordered_the_same_way_missing', [
                    'handle' => $handle,
                ]));
            }

            $collectionSortField = $collection->sortField() ? $collection->sortField() : null;
            if ($collection->dated()) {
                $collectionSortField = 'date';
            }

            if (! $sortField) {
                $sortField = $collectionSortField;
            } elseif ($sortField !== $collectionSortField) {
                $fail(__('feedamic::validation.collections_ordered_the_same_way'));
            }
        }
    }
}
