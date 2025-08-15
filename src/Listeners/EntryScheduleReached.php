<?php

namespace MityDigital\Feedamic\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use MityDigital\Feedamic\Facades\Feedamic;

class EntryScheduleReached implements ShouldQueue
{
    public function handle(EntryScheduleReached $event)
    {
        Feedamic::clearCache(
            collection: $event->entry->collection()->handle()
        );
    }
}
