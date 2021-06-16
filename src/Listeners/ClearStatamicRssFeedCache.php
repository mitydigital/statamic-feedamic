<?php

namespace MityDigital\StatamicRssFeed\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\EntrySaved;

class ClearStatamicRssFeedCache implements ShouldQueue
{
    /**
     * Simply clear the Statamic RSS Feed caches
     */
    public function handle(EntrySaved $event)
    {
        if (in_array($event->entry->collection()->handle(), config('statamic.rss.collections'))) {
            Cache::forget(config('statamic.rss.cache'));
            Cache::forget(config('statamic.rss.cache.atom'));
            Cache::forget(config('statamic.rss.cache.rss'));
        }
    }
}
