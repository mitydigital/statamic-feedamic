<?php

namespace MityDigital\Feedamic\Tags;

use Statamic\Tags\Tags;

class Feedamic extends Tags
{
    /**
     * Tag {{ feedamic }} outputs the configured feeds for auto-discovery
     *
     * @return string
     */
    public function index()
    {
        $links = [];
        foreach (feedamic.routes') as $type => $route) {
            $mime = 'application/xml';
            switch($type)
            {
                case 'atom':
                    $mime = 'application/atom+xml';
                    break;
                case 'rss':
                    $mime = 'application/rss+xml';
                    break;
            }

            $links[] = '<link rel="alternate" type="'.$mime.'" title="'.feedamic.title').'"  href="'.$route.'" />';
        }

        return implode("\r\n", $links);
    }
}