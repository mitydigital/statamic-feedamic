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
        // v2.1 cache clearing behaviour
        if (in_array($event->entry->collection()->handle(), config('feedamic.collections', []))) {
            Cache::forget(config('feedamic.cache'));
            Cache::forget(config('feedamic.cache').'.atom');
            Cache::forget(config('feedamic.cache').'.rss');
        }

        // v2.2 cache clearing for multiple feeds
        // look at specific feeds if we have them configured
        foreach (config('feedamic.feeds', []) as $feed => $config) {
            if (isset($config['collections']) && is_array($config['collections']) && in_array($event->entry->collection()->handle(),
                    $config['collections'])) {
                foreach ($config['routes'] as $type => $route) {
                    Cache::forget(config('feedamic.cache').'.'.$feed.'.'.$type);
                    Cache::forget(config('feedamic.cache').'.'.$feed);
                }
            }
        }
    }
}
