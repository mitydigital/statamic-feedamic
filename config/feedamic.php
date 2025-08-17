<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Path and Filename
    |--------------------------------------------------------------------------
    |
    | Applies to Statamic 5 only.
    |
    | Your Feedamic config gets stored in a YAML file, and by default is
    | in your 'resources/addons' folder for Statamic 6 support,
    | '/resources/addons/feedamic.yaml'.
    |
    | You can change the path and filename here, if required.
    |
    */

    'path' => resource_path('addons'),

    'filename' => 'feedamic',

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | The base cache key for output, and will be combined with the site and handle
    | when caching takes place, allowing for individual cache control.
    |
    | Will be cached forever until EventSaved is fired, or you manually clear the cache.
    |
    */

    'cache' => 'feedamic',

    'cache_enabled' => env('FEEDAMIC_CACHE_ENABLED', true),
];
