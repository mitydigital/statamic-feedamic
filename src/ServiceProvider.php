<?php

namespace MityDigital\Feedamic;

use MityDigital\Feedamic\Commands\ClearCacheCommand;
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
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $tags = [
        Feedamic::class,
    ];

    protected $updateScripts = [
        // v2.0.1
        \MityDigital\Feedamic\UpdateScripts\v2_0_1\MoveConfigFile::class
    ];
}
