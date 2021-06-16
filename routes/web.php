<?php

collect(config('statamic.rss.routes'))->each(function ($route, $type) {
    Route::get($route, 'StatamicRssFeedController@' . $type);
});
