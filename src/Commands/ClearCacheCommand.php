<?php

namespace MityDigital\Feedamic\Commands;

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
    protected $signature = 'statamic:feedamic:clear';

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
        Cache::forget(config('feedamic.cache'));
        Cache::forget(config('feedamic.cache').'.atom');
        Cache::forget(config('feedamic.cache').'.rss');

        $this->info('Ah-choo... it\'s all gone.');
    }
}
