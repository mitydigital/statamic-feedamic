<?php

namespace MityDigital\StatamicRssFeed\Tags;

use Statamic\Tags\Tags;

class RssAutoDiscovery extends Tags
{
    /**
     * Tag {{ rss_auto_discovery }} outputs the configured feeds for auto-discovery
     *
     * @return string
     */
    public function index()
    {
        $links = [];
        foreach (config('statamic.rss.routes') as $type => $route) {
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

            $links[] = '<link rel="alternate" type="'.$mime.'" title="'.config('statamic.rss.title').'"  href="'.$route.'" />';
        }

        return implode("\r\n", $links);
    }
}