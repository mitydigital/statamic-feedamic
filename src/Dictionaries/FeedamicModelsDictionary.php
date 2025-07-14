<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Contracts\FeedamicEntry;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;

class FeedamicModelsDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType('Models', FeedamicEntry::class, function (string $class, string $requiredClass): bool {
            if (in_array($requiredClass, class_implements($class)) && in_array(\Illuminate\Support\Traits\ForwardsCalls::class, class_uses($class))) {
                return true;
            }

            return is_subclass_of($class, \MityDigital\Feedamic\Models\FeedamicEntry::class);
        }))
            ->map(fn ($scope) => [
                'label' => $scope,
                'value' => $scope,
            ])
            ->prepend([
                'label' => 'MityDigital\Feedamic\Models\FeedEntry',
                'value' => 'MityDigital\Feedamic\Models\FeedEntry',
            ])
            ->toArray();
    }
}
