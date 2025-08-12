<?php

namespace MityDigital\Feedamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use MityDigital\Feedamic\Facades\Feedamic;

class ScheduledCacheInvalidated implements ShouldQueue
{
    public function handle(\MityDigital\StatamicScheduledCacheInvalidator\Events\ScheduledCacheInvalidated $event)
    {
        foreach ($event->collections as $collection) {
            Feedamic::clearCache(
                collection: $collection
            );
        }
    }
}
