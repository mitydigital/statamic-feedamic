<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;
use Statamic\Query\Scopes\Scope;

class FeedamicScopesDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType(Scope::class))
            ->map(fn ($scope) => [
                'label' => $scope,
                'value' => $scope,
            ])
            ->toArray();
    }
}
