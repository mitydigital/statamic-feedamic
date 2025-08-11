<?php

namespace MityDigital\Feedamic;

use MityDigital\Feedamic\Commands\ClearCacheCommand;
use MityDigital\Feedamic\Listeners\ClearFeedamicCache;
use MityDigital\Feedamic\Listeners\ScheduledCacheInvalidated;
use MityDigital\Feedamic\Tags\Feedamic;
use Statamic\Events\EntrySaved;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    // protected $viewNamespace = 'mitydigital/feedamic';

    /*protected $commands = [
        ClearCacheCommand::class,
    ];*/

    protected $listen = [
        EntrySaved::class => [
            ClearFeedamicCache::class,
        ],
        \MityDigital\StatamicScheduledCacheInvalidator\Events\ScheduledCacheInvalidated::class => [
            ScheduledCacheInvalidated::class,
        ],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $tags = [
        Feedamic::class,
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/mitydigital/feedamic'),
        ], 'feedamic-views');

        $this->app->bind('Feedamic', function () {
            return new Support\Feedamic;
        });

        Nav::extend(function ($nav) {
            $nav->tools(__('feedamic::cp.nav'))
                ->route('feedamic.config.show')
                ->icon(Facades\Feedamic::svg('feedamic'))
                ->can('feedamic.config');
        });

        // register permission
        Permission::register('feedamic.config')
            ->label(__('feedamic::cp.permission.label'))
            ->description(__('feedamic::cp.permission.description'));
    }
}
