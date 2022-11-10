<?php

namespace MityDigital\Feedamic;

use MityDigital\Feedamic\Commands\ClearCacheCommand;
use MityDigital\Feedamic\Feedamic as FeedamicFeedamic;
use MityDigital\Feedamic\Listeners\ClearFeedamicCache;
use MityDigital\Feedamic\Tags\Feedamic;
use Statamic\Events\EntrySaved;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'mitydigital/feedamic';

    protected $commands = [
        ClearCacheCommand::class
    ];

    protected $listen = [
        EntrySaved::class => [
            ClearFeedamicCache::class,
        ]
    ];

    protected $routes = [
        'web' => __DIR__ . '/../routes/web.php',
    ];

    protected $tags = [
        Feedamic::class,
    ];

    protected $updateScripts = [
        // v2.1.0
        \MityDigital\Feedamic\UpdateScripts\v2_1_0\MoveConfigFile::class,

        // v2.2.0
        \MityDigital\Feedamic\UpdateScripts\v2_2_0\CheckForViews::class,
    ];

    public function bootAddon()
    {
        $this->app->singleton(FeedamicFeedamic::class);
        $this->app->alias(FeedamicFeedamic::class, 'feedamic');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mitydigital/feedamic'),
        ], 'feedamic-views');
    }

    protected function bootConfig()
    {
        $filename = $this->getAddon()->slug();
        $directory = $this->getAddon()->directory();
        $origin = "{$directory}config/{$filename}.php";

        if (!$this->config || !file_exists($origin)) {
            return $this;
        }

        // DO NOT MERGE CONFIG
        // We don't want to use anything from the default - require the user to do it all themselves
        // Added in v2.2
        //$this->mergeConfigFrom($origin, $filename);

        $this->publishes([
            $origin => config_path("{$filename}.php"),
        ], "{$filename}-config");

        return $this;
    }
}
