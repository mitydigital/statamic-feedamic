<?php

namespace MityDigital\Feedamic\Tags;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Facades\Site;
use Statamic\Tags\Tags;

class Feedamic extends Tags
{
    public function index(): string
    {
        $currentSite = Site::current();

        // get feeds for the current site
        $feeds = \MityDigital\Feedamic\Facades\Feedamic::getFeedsForSite($currentSite->handle());

        return $this->buildFeeds($currentSite, $feeds);
    }

    protected function buildFeeds(\Statamic\Sites\Site $site, Collection $feeds): string
    {
        $links = [];

        foreach ($feeds as $feed) {
            foreach (\MityDigital\Feedamic\Facades\Feedamic::getFeedTypes() as $feedType) {
                if ($url = Arr::get($feed['routes'], $feedType)) {
                    $mime = match ($feedType) {
                        'atom' => 'application/atom+xml',
                        'rss' => 'application/rss+xml',
                        default => 'application/xml',
                    };

                    $route = $site->absoluteUrl().$url;

                    $links[] = '<link rel="alternate" type="'.$mime.'" title="'.$feed['title'].'" href="'.$route.'" />';
                }
            }
        }

        return implode("\r\n", $links);
    }

    public function wildcard($feed): string
    {
        $currentSite = Site::current();

        // get feed for the current site and handle
        $config = \MityDigital\Feedamic\Facades\Feedamic::getFeedsForSite($currentSite->handle())
            ->first(fn (array $config) => $config['handle'] === $feed);

        if (! $config) {
            return '';
        }

        return $this->buildFeeds($currentSite, collect([$config]));
    }
}
