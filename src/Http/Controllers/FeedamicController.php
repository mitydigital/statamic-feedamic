<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Statamic\Facades\Site;

class FeedamicController
{
    public function __invoke()
    {
        echo 'feedamic::'.Site::current()->handle();
    }
}
