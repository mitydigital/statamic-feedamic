<?php

use MityDigital\Feedamic\Http\CP\Controllers\FeedamicConfigurationController;

Route::get('feedamic', [FeedamicConfigurationController::class, 'show'])
    ->name('feedamic.config.show');

Route::post('feedamic', [FeedamicConfigurationController::class, 'update'])
    ->name('feedamic.config.update');
