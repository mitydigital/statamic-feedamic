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

        if (config()->has('feedamic.routes')) {
            foreach (config('feedamic.routes') as $type => $route) {
                $mime = 'application/xml';
                switch ($type) {
                    case 'atom':
                        $mime = 'application/atom+xml';
                        break;
                    case 'rss':
                        $mime = 'application/rss+xml';
                        break;
                }

                $links[] = '<link rel="alternate" type="'.$mime.'" title="'.config('feedamic.title').'"  href="'.$route.'" />';
            }
        }

        // v2.2 multiple feeds support
        foreach (config('feedamic.feeds', []) as $feed => $config) {
            foreach ($config['routes'] as $type => $route) {
                $mime = 'application/xml';
                switch ($type) {
                    case 'atom':
                        $mime = 'application/atom+xml';
                        break;
                    case 'rss':
                        $mime = 'application/rss+xml';
                        break;
                }

                $links[] = '<link rel="alternate" type="'.$mime.'" title="'.$config['title'].'"  href="'.$route.'" />';
            }
        }

        return implode("\r\n", $links);
    }

    /**
     * Tag {{ feedamic:feed }} to allow only the specific feed routes to be returned
     *
     * @param $feed
     * @return string
     */
    public function wildcard($feed)
    {
        $links = [];

        // does the feed exist?
        if (config()->has('feedamic.feeds.'.$feed)) {
            foreach (config('feedamic.feeds.'.$feed.'.routes', []) as $type => $route) {
                $mime = 'application/xml';
                switch ($type) {
                    case 'atom':
                        $mime = 'application/atom+xml';
                        break;
                    case 'rss':
                        $mime = 'application/rss+xml';
                        break;
                }

                $links[] = '<link rel="alternate" type="'.$mime.'" title="'.config('feedamic.feeds.'.$feed.'.title').'"  href="'.$route.'" />';
            }
        }

        return implode("\r\n", $links);
    }
}