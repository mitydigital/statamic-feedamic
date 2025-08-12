<?php

namespace MityDigital\Feedamic\Commands;

use Illuminate\Console\Command;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Console\RunsInPlease;

class ClearCacheCommand extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:feedamic:clear 
                            {--handles= : Optional, the feed handles you want to clear, separated by commas. When omitted, will clear all feeds.} 
                            {--sites= : Optional, the sites you want to clear, separated by commas. When omitted, will clear all sites.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Feedamic caches';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $handles = $this->option('handles') ? explode(',', $this->option('handles')) : null;
        $sites = $this->option('sites') ? explode(',', $this->option('sites')) : null;

        $this->info(sprintf(
            'Clearing Feedamic cache for %s in %s.',
            $handles ? implode(', ', $handles) : 'all feeds',
            $sites ? implode(', ', $sites) : 'all sites',
        ));

        $clearedFeeds = Feedamic::clearCache(
            handles: $handles,
            sites: $sites
        );

        foreach ($clearedFeeds as $clearedFeed) {
            $this->info(sprintf('Cleared %s', $clearedFeed));
        }

        $this->info('Ah-choo... Feedamic\'s cache is all gone.');
    }
}
