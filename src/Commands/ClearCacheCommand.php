<?php

namespace MityDigital\StatamicRssFeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Stache;

class ClearCacheCommand extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:rss-cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Statamic RSS Feed caches';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Cache::forget(config('statamic.rss.cache'));
        Cache::forget(config('statamic.rss.cache').'.atom');
        Cache::forget(config('statamic.rss.cache').'.rss');

        $this->info('Ah-choo... it\'s all gone.');
    }
}
