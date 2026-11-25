<?php

namespace MityDigital\Feedamic\Listeners;

use MityDigital\Feedamic\Facades\Feedamic;

class AddonSettingsSaved
{
    /**
     * Clear all feeds when Feedamic's settings are saved. Not queued, so that the new settings are
     * used straight away.
     */
    public function handle(\Statamic\Events\AddonSettingsSaved $event): void
    {
        if ($event->settings->addon()->id() !== 'mitydigital/feedamic') {
            return;
        }

        Feedamic::load(true);
        Feedamic::clearCache();
    }
}
