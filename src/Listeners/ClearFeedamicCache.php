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
        if (in_array($event->entry->collection()->handle(), feedamic.collections'))) {
            Cache::forget(feedamic.cache'));
            Cache::forget(feedamic.cache').'.atom');
            Cache::forget(feedamic.cache').'.rss');
        }
    }
}
