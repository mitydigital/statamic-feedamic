<?php

collect(config('statamic.feedamic.routes'))->each(function ($route, $type) {
    Route::get($route, 'StatamicRssFeedController@' . $type);
});
