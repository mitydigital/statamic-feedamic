<?php

namespace MityDigital\StatamicRssFeed;

use MityDigital\StatamicRssFeed\Commands\ClearCacheCommand;
use MityDigital\StatamicRssFeed\Listeners\ClearStatamicRssFeedCache;
use MityDigital\StatamicRssFeed\Tags\RssAutoDiscovery;
use Statamic\Events\EntrySaved;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'mitydigital/statamic-rss-feed';

    protected $commands = [
        ClearCacheCommand::class
    ];

    protected $listen = [
        EntrySaved::class => [
            ClearStatamicRssFeedCache::class,
        ]
    ];

    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $tags = [
        RssAutoDiscovery::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->mergeConfigFrom(__DIR__.'/../config/rss.php', 'statamic.rss');

        $this->publishes([
            __DIR__.'/../config/rss.php' => config_path('statamic/rss.php')
        ], 'config');
    }
}
