<?php

namespace MityDigital\Feedamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Events\EntrySaved;

class ClearFeedamicCache implements ShouldQueue
{
    /**
     * Simply clear the Feedamic RSS Feed caches
     */
    public function handle(EntrySaved $event)
    {
        Feedamic::clearCache(
            sites: [$event->entry->locale],
            collection: $event->entry->collection()->handle()
        );
    }
}
