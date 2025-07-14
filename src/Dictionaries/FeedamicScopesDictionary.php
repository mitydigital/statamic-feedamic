<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;

class FeedamicScopesDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType('Scopes', \Statamic\Query\Scopes\Scope::class))
            ->map(fn ($scope) => [
                'label' => $scope,
                'value' => $scope,
            ])
            ->toArray();
    }
}
