<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Contracts\FeedamicAuthor;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;

class FeedamicAuthorModelsDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType('Models', FeedamicAuthor::class, function (string $class, string $requiredClass): bool {
            if (in_array($requiredClass, class_implements($class)) && in_array(\Illuminate\Support\Traits\ForwardsCalls::class, class_uses($class))) {
                return true;
            }

            return is_subclass_of($class, \MityDigital\Feedamic\Models\FeedamicAuthor::class);
        }))
            ->map(fn ($scope) => [
                'label' => $scope,
                'value' => $scope,
            ])
            ->prepend([
                'label' => 'MityDigital\Feedamic\Models\FeedamicAuthor',
                'value' => 'MityDigital\Feedamic\Models\FeedamicAuthor',
            ])
            ->toArray();
    }
}
