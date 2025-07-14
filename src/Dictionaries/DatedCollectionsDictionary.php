<?php

namespace MityDigital\Feedamic\Dictionaries;

use Statamic\Dictionaries\BasicDictionary;

class DatedCollectionsDictionary extends BasicDictionary
{
    protected function getItems(): array
    {
        return \Statamic\Facades\Collection::all()
            ->filter(fn (\Statamic\Entries\Collection $collection) => $collection->dated())
            ->values()
            ->map(fn (\Statamic\Entries\Collection $collection) => [
                'label' => $collection->title(),
                'value' => $collection->id(),
            ])
            ->toArray();
    }
}
