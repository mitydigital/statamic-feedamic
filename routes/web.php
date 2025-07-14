<?php

use Illuminate\Support\Arr;
use MityDigital\Feedamic\Facades\Feedamic;
use MityDigital\Feedamic\Http\Controllers\FeedamicController;

$routes = Feedamic::getRoutes();

foreach (Arr::get($routes, 'domains', []) as $domain => $domainRoutes) {
    Route::domain($domain)->group(function () use ($domainRoutes) {
        foreach ($domainRoutes as $domainRoute) {
            Route::get($domainRoute, FeedamicController::class);
        }
    });
}

foreach (Arr::get($routes, 'default', []) as $route) {
    Route::get($route, FeedamicController::class);
}
