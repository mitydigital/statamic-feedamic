<?php

use MityDigital\Feedamic\Http\Controllers\FeedamicController;
use MityDigital\Feedamic\Feedamic;

// if we are on or above 2.2.0, and we have a 'feeds' config param
if (Feedamic::version() >= '2.2.0' && config('feedamic.feeds', false)) {
    // v2.2 and above (support for multiple feeds)
    foreach (config('feedamic.feeds') as $key => $feed) {
        if (isset($feed['routes']) && is_array($feed['routes'])) {
            collect($feed['routes'])->each(function ($route, $type) use ($key) {
                // Create a route for the given feed and type
                Route::get($route, [FeedamicController::class, $type])
                    ->setDefaults(['feed' => $key])
                    ->name('feedamic.'.$key.'.'.$type);
            });
        }
    }
} else {
    // v2.1 and below
    collect(config('feedamic.routes'))->each(function ($route, $type) {
        Route::get($route, [FeedamicController::class, $type]);
    });
}
