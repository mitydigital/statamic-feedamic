<?php

namespace MityDigital\Feedamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use Statamic\Fields\Blueprint;

/**
 * @method static Blueprint blueprint()
 * @method static array getClassOfType(string $folder, string $requiredClass)
 * @method static array getFeedTypes()
 * @method static string getPath()
 * @method static array getRoutes()
 * @method static Collection getFeeds()
 * @method static Collection getFeedsForSite(string $handle)
 * @method static array getConfig(string $path, string $site)
 * @method static array load()
 * @method static LazyCollection getEntries(array $config)
 * @method static void save(array $payload)
 *
 * @see \MityDigital\Feedamic\Support\Feedamic
 */
class Feedamic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MityDigital\Feedamic\Support\Feedamic::class;
    }
}
