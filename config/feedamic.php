<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Path and Filename
    |--------------------------------------------------------------------------
    |
    | Your Feedamic config gets stored in a YAML file, and by default is
    | in your 'content' folder, '/content/feedamic.yaml'. We love
    | this because it means it gets included in Statamic's Git Automation.
    |
    | You can change the path and filename here, if required.
    |
    */

    'path' => base_path('content'),

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
];
