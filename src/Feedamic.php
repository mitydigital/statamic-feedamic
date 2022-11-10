<?php

namespace MityDigital\Feedamic;

use Statamic\Facades\Addon;
use Statamic\Facades\Site;

class Feedamic
{
    public static function version(): string
    {
        return Addon::get('mitydigital/feedamic')->version();
    }

    /**
     * Some helper logic to get the config (or a fallback) from the Feedamic config array
     *
     * @param  string|null  $feed
     * @param  string  $key
     * @param  mixed|null  $default
     * @param  bool|null  $useDefaultIfEmpty
     * @return \Illuminate\Config\Repository|Application|mixed
     */
    public function getConfigValue(
        string|null $feed,
        string $key,
        mixed $default = null,
        bool $useDefaultIfEmpty = null
    ) {
        // set the location
        $location = '';

        // do we have a feed, and does the "feeds" config exist?
        if (static::version() >= '2.2.0' && $feed && config('feedamic.feeds', false)) {
            // if so, does the key exist in there?
            if (config()->has('feedamic.feeds.' . $feed . '.' . $key)) {
                $location = 'feedamic.feeds.' . $feed . '.' . $key;
            }
        }

        if (!$location) {
            // no 'feeds', so look for the core value
            $location = 'feedamic.' . $key;
        }

        $value = config($location, $default);

        if ($useDefaultIfEmpty && $default && !$value) {
            return $default;
        }

        return $value;
    }

    /**
     * Get the cache key for a feed.
     * Depending on the addon's version and provided parameters, the cache key is extended by them.
     *
     * @param string $feed
     * @param string $type
     * @param string $forcedLocale
     * @return string
     */
    public function getCacheKey(string|null $feed = '', string $type = '', string $forcedLocale = '')
    {
        // conditionally build up an array of cache key parts
        $cacheKeyParts = [];

        $cacheKeyParts[] = config('feedamic.cache');

        if (static::version() >= '2.2.0' && $feed) {
            $cacheKeyParts[] = $feed;
        }

        if ($forcedLocale) {
            $cacheKeyParts[] = $forcedLocale;
        } else {
            $cacheKeyParts[] = $this->getLocalesCacheKey($feed);
        }

        if ($type) {
            $cacheKeyParts[] = $type;
        }

        // glue the cache key parts together to the actual string
        return implode('.', $cacheKeyParts);
    }

    /**
     * Get the locale/s for a feed
     *
     * @param string $feed
     * @return string|array
     */
    public function getLocales(string|null $feed = '')
    {
        // filter entries by their locales; include all locales by default
        $locales = $this->getConfigValue($feed, 'locales', '*', true);

        // dynamically get current site handle for special locales value 'current'
        return $locales === 'current' ? [Site::current()->handle()] : $locales;
    }

    /**
     * Get the configured collections for a feed.
     *
     * @param string $feed
     * @return array
     */
    public function getCollections(string|null $feed = '')
    {
        // v2.2 or above
        if (static::version() >= '2.2.0' && $feed) {
            return config('feedamic.feeds.' . $feed . '.collections');
        }

        // v2.1 or below
        return config('feedamic.collections');
    }

    /**
     * Get the cache key part for the configured locales.
     *
     * @param string|null $feed
     * @return string
     */
    public function getLocalesCacheKey(string|null $feed = '')
    {
        $locales = $this->getLocales($feed);

        if (is_array($locales)) {
            $localesCacheKey = implode('.', $locales);
        } else {
            $localesCacheKey = $locales;
        }

        return $localesCacheKey;
    }

    /**
     * Returns an array of the feed's cache keys.
     *
     * There are 3 scenarios to handle:
     *
     * 1: 'current'
     * The feed should only contain entries from the current site.
     * On a multisite this means, that there might be cache keys for every site's handle:
     * feedamic.news.us, feedamic.news.fr, feedamic.news.de, ...
     * Due to this dynamic nature of the 'current' configuration, it is necessary to clear
     * the caches for all the site handles.
     *
     * 2: '*'
     * The feed contains entries from all sites.
     * As only a single cache key is needed - feedamic.news.* - only this sinlge cache key
     * has to be cleared.
     *
     * 3: custom array of locales, e. g. ['com', 'fr']
     * The feed contains entries from custom locales.
     * In its current implementation, the cache key for all site's feeds is the same.
     * Therefore only this single cache key - feedamic.news.com.fr - has to be invalidated.
     *
     * @param string $feed
     * @param array $types
     * @return array
     */
    public function getCacheClearingKeys(string|null $feed, array $types)
    {
        $cacheKeys = collect();

        $feedLocales = $this->getConfigValue($feed, 'locales', '*', true);

        switch ($feedLocales) {
            case 'current':
                $cacheLocales = Site::all()->map(function ($site) {
                    return $site->handle();
                })->values();
                break;

            case '*':
                $cacheLocales = ['*'];
                break;

            default:
                $cacheLocales = [implode('.', $feedLocales)];
        }

        foreach ($types as $type) {
            foreach ($cacheLocales as $cacheLocale) {
                $cacheKeys->push($this->getCacheKey($feed, $type, $cacheLocale));
                $cacheKeys->push($this->getCacheKey($feed, '', $cacheLocale));
            }
        }

        return $cacheKeys->unique();
    }
}
