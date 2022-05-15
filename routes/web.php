<?php

collect(config('feedamic.routes'))->each(function ($route, $type) {
    Route::get($route, 'FeedamicController@' . $type);
});
