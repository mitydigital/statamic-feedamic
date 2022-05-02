<?php

namespace MityDigital\Feedamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\EntrySaved;

class ClearFeedamicCache implements ShouldQueue
{
    /**
     * Simply clear the Feedamic RSS Feed caches
     */
    public function handle(EntrySaved $event)
    {
        if (in_array($event->entry->collection()->handle(), config('statamic.feedamic.collections'))) {
            Cache::forget(config('statamic.feedamic.cache'));
            Cache::forget(config('statamic.feedamic.cache').'.atom');
            Cache::forget(config('statamic.feedamic.cache').'.rss');
        }
    }
}
