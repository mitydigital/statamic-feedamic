<?php

namespace MityDigital\Feedamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static string version()
 * @method static \Illuminate\Config\Repository|Application|mixed getConfigValue(string|null $feed, string $key, mixed|null $default = null, bool|null $useDefaultIfEmpty = null)
 * @method static string getCacheKey(string|null $feed = '', string $type = '', string $forcedLocale = '')
 * @method static string|array getLocales(string|null $feed = '')
 * @method static array getCollection(string|null $feed = '')
 * @method static string getLocalesCacheKey(string|null $feed = '')
 * @method static getCacheClearingKeys array(string|null $feed, array $types)
 *
 * @see \MityDigital\Feedamic\Feedamic
 */
class Feedamic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'feedamic';
    }
}
