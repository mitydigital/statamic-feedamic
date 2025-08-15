<?php

namespace MityDigital\Feedamic\Dictionaries;

use MityDigital\Feedamic\Abstracts\AbstractFeedamicAuthor;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Dictionaries\BasicDictionary;

class FeedamicAuthorModelsDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return collect(Feedamic::getClassOfType(AbstractFeedamicAuthor::class))
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
