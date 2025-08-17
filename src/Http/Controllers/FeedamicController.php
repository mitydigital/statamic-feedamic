<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Site;

class FeedamicController
{
    public function __invoke()
    {
        $route = Str::remove(Site::current()->absoluteUrl(), request()->url());

        $config = Feedamic::getConfig(
            path: $route,
            site: Site::current()->handle()
        );

        // if there's no config, abort!
        if (! $config) {
            throw new NotFoundHttpException;
        }

        return Response::make(Feedamic::render($config, $route), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
