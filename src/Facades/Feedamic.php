<?php

namespace MityDigital\Feedamic\Facades;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use MityDigital\Feedamic\Models\FeedamicConfig;
use Statamic\Fields\Blueprint;

/**
 * @method static Blueprint blueprint()
 * @method static array getClassOfType(string $folder, string $requiredClass)
 * @method static array getFeedTypes()
 * @method static string getPath()
 * @method static array getRoutes()
 * @method static Collection getFeeds()
 * @method static Collection getFeedsForSite(string $handle)
 * @method static ?FeedamicConfig getConfig(string $path, string $site)
 * @method static array load(bool $refresh = false)
 * @method static LazyCollection getEntries(array $config)
 * @method static void save(array $payload)
 * @method static string string svg(string $name, string $attrs = null)
 * @method static array clearCache(?array $handles = null, ?array $sites = null, ?string $collection = null)
 * @method static void modify(string $fieldHandle, Closure $modifier, ?Closure $when = null, ?array $feeds = null)
 * @method static void removeModifier(string $fieldHandle)
 * @method static ?Closure getModifier(AbstractFeedamicEntry $feedamicEntry, string $fieldHandle, mixed $value)
 * @method static string render(FeedamicConfig $config, string $route)
 * @method static string version()
 * @method static bool includeCpRoutes()
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
