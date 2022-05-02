<?php

collect(config('statamic.feedamic.routes'))->each(function ($route, $type) {
    Route::get($route, 'FeedamicController@' . $type);
});
