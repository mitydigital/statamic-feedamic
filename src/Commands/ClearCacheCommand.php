<?php

namespace MityDigital\Feedamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\RunsInPlease;

class ClearCacheCommand extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:feedamic:clear {feeds?* : Optional, the feed keys you want to flush. Can be multiple keys by space, or omit to clear everything.}';

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
        $feeds = $this->argument('feeds');
        if (count($feeds) > 0) {
            // clear the specific feeds only
            $cleared = [];
            foreach ($feeds as $feed) {
                foreach (config('feedamic.feeds.'.$feed.'.routes', []) as $type => $route) {
                    Cache::forget(config('feedamic.cache').'.'.$feed.'.'.$type);
                    Cache::forget(config('feedamic.cache').'.'.$feed);
                    if (!in_array('"'.$feed.'"', $cleared)) {
                        $cleared[] = '"'.$feed.'"';
                    }
                }
            }

            // make it prettier
            // Arr::join came in Laravel 9 - so do it manually for L8 support
            if (count($cleared) > 1) {
                $lastCleared = array_pop($cleared);
                $cleared = implode(', ', $cleared).' and '.$lastCleared;
            } else {
                $cleared = end($cleared);
            }

            $this->info('Ah-choo... feeds for '.$cleared.' are clear.');
        } else {
            // Clear specific feeds caches
            foreach (config('feedamic.feeds', []) as $feed => $config) {
                foreach ($config['routes'] as $type => $route) {
                    Cache::forget(config('feedamic.cache').'.'.$feed.'.'.$type);
                    Cache::forget(config('feedamic.cache').'.'.$feed);
                }
            }

            // Pre-2.2 clearing of cache
            Cache::forget(config('feedamic.cache'));
            Cache::forget(config('feedamic.cache').'.atom');
            Cache::forget(config('feedamic.cache').'.rss');

            $this->info('Ah-choo... it\'s all gone.');
        }
    }
}
