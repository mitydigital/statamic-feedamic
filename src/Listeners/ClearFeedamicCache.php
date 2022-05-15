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
        if (in_array($event->entry->collection()->handle(), config('feedamic.collections'))) {
            Cache::forget(config('feedamic.cache'));
            Cache::forget(config('feedamic.cache').'.atom');
            Cache::forget(config('feedamic.cache').'.rss');
        }
    }
}
