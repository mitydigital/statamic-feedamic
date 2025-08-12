<?php

namespace MityDigital\Feedamic\Commands;

use Illuminate\Console\Command;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Console\RunsInPlease;

class ClearCacheCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:feedamic:clear 
                            {--handles= : Optional, the feed handles you want to clear, separated by commas. When omitted, will clear all feeds.} 
                            {--sites= : Optional, the sites you want to clear, separated by commas. When omitted, will clear all sites.}';

    protected $description = 'Clear the Feedamic caches';

    public function handle()
    {
        $handles = $this->option('handles') ? explode(',', $this->option('handles')) : null;
        $sites = $this->option('sites') ? explode(',', $this->option('sites')) : null;

        $this->info(__('feedamic::command.clear.start', [
            'feeds' => $handles ? implode(', ', $handles) : __('feedamic::command.clear.all.feeds'),
            'sites' => $sites ? implode(', ', $sites) : __('feedamic::command.clear.all.sites'),
        ]));

        $clearedFeeds = Feedamic::clearCache(
            handles: $handles,
            sites: $sites
        );

        foreach ($clearedFeeds as $clearedFeed) {
            $this->info(__('feedamic::command.clear.cleared', [
                'key' => $clearedFeed,
            ]));
        }

        $this->info(__('feedamic::command.clear.done'));
    }
}
