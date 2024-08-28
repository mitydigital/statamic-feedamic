<?php

namespace MityDigital\Feedamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use MityDigital\Feedamic\Commands\ClearCacheCommand;
use Statamic\Events\EntrySaved;

class ScheduledCacheInvalidated implements ShouldQueue
{
    public function handle(\MityDigital\StatamicScheduledCacheInvalidator\Events\ScheduledCacheInvalidated $event) {
        $feeds = collect(config('feedamic.feeds'))
            ->filter(function (array $config, string $key) use ($event) {
                return array_intersect(Arr::get($config, 'collections', []), $event->collections);
            })
        ->keys();

        Artisan::call(ClearCacheCommand::class, [
            'feeds' => $feeds
        ]);
    }
}