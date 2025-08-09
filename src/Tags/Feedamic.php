<?php

namespace MityDigital\Feedamic\Tags;

use Illuminate\Support\Collection;
use MityDigital\Feedamic\Models\FeedamicConfig;
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
        $usedRoutes = [];

        return $feeds
            ->map(function (FeedamicConfig $config) use ($site, &$usedRoutes) {
                $links = [];

                foreach ($config->getRoutes() as $feedType => $route) {
                    $mime = match ($feedType) {
                        'atom' => 'application/atom+xml',
                        'rss' => 'application/rss+xml',
                        default => 'application/xml',
                    };

                    $absoluteRoute = $site->absoluteUrl().$route;

                    if (! in_array($absoluteRoute, $usedRoutes)) {
                        $links[] = sprintf(
                            '<link rel="alternate" type="%s" title="%s" href="%s" />',
                            $mime,
                            $config->title,
                            $absoluteRoute
                        );

                        $usedRoutes[] = $absoluteRoute;
                    }
                }

                return $links;
            })
            ->flatten()
            ->join("\r\n");
    }

    public function wildcard($feed): string
    {
        $currentSite = Site::current();

        // get feed for the current site and handle
        $config = \MityDigital\Feedamic\Facades\Feedamic::getFeedsForSite($currentSite->handle())
            ->first(fn (FeedamicConfig $config) => $config->handle === $feed);

        if (! $config) {
            return '';
        }

        return $this->buildFeeds($currentSite, collect([$config]));
    }
}
