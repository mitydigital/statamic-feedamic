<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;

class FeedamicEntryModelsDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType(AbstractFeedamicEntry::class))
            ->map(fn ($scope) => [
                'label' => $scope,
                'value' => $scope,
            ])
            ->prepend([
                'label' => 'MityDigital\Feedamic\Models\FeedamicEntry',
                'value' => 'MityDigital\Feedamic\Models\FeedamicEntry',
            ])
            ->toArray();
    }
}
