<?php

namespace MityDigital\Feedamic\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MityDigital\Feedamic\Exceptions\FeedNotConfiguredException;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\Path;
use Statamic\Sites\Site;
use Statamic\Support\Str;

class FeedamicConfig
{
    public string $handle;

    public ?string $title;

    public ?string $description;

    public array $collections;

    public array $taxonomies = [];

    public array $sites = [];

    public array $routes = [];

    public ?int $limit = null;

    public ?string $alt_url = null;

    public ?string $copyright = null;

    public ?string $author_fallback_name;

    public ?string $author_fallback_email;

    public array $mappings = [
        'title' => [],
        'summary' => [],
        'content' => [],
        'image' => null,
        'image_width' => null,
        'image_height' => null,
        'author_type' => null,
        'author_field' => null,
        'author_name' => null,
        'author_email' => null,
    ];

    public ?string $scope = null;

    public string $author_model = FeedamicAuthor::class;

    public string $entry_model = FeedamicEntry::class;

    public function __construct(array $feed, Collection $defaults)
    {
        $this->handle = Arr::get($feed, 'handle');
        $this->title = Arr::get($feed, 'title');
        $this->description = Arr::get($feed, 'description');
        $this->collections = Arr::get($feed, 'collections', []);
        $this->alt_url = Arr::get($feed, 'alt_url');

        if ($author_model = Arr::get($feed, 'author_model')) {
            $this->author_model = $author_model;
        }

        if ($entry_model = Arr::get($feed, 'entry_model')) {
            $this->entry_model = $entry_model;
        }

        if ($scope = Arr::get($feed, 'scope')) {
            $this->scope = $scope;
        }

        if ($taxonomies = Arr::get($feed, 'taxonomies')) {
            if (is_array($taxonomies) && ! empty($taxonomies)) {
                $this->taxonomies = $taxonomies;
            }
        }

        // update limit
        if (Arr::get($feed, 'show', 'all') === 'limit') {
            $this->limit = (int) Arr::get($feed, 'show_limit');
        }

        // update site config
        if (Arr::get($feed, 'sites') === 'all') {
            $this->sites = \Statamic\Facades\Site::all()->map(fn (Site $site) => $site->handle())->values()->toArray();
        } else {
            $this->sites = Arr::get($feed, 'sites_specific', []);
        }

        // update routes
        foreach (Feedamic::getFeedTypes() as $feedType) {
            $this->routes[$feedType] = null;
            $this->routes[$feedType.'_view'] = null;
            if (isset($feed['routes']) && $route = Arr::get($feed['routes'], $feedType)) {
                $this->routes[$feedType] = $route;
                $view = 'feedamic::'.$feedType;
                if ($override = Arr::get($feed['routes'], $feedType.'_view')) {
                    $view = $override;
                }
                $this->routes[$feedType.'_view'] = $view;
            }
        }

        // copyright
        $this->copyright = match (Arr::get($feed, 'copyright_mode')) {
            'default' => Arr::get($defaults, 'default_copyright'),
            'custom' => Arr::get($feed, 'copyright'),
            default => null
        };

        // fallback author
        if (Arr::get($feed, 'author_fallback_mode') === 'custom') {
            $this->author_fallback_name = Arr::get($feed, 'author_fallback_name');
            $this->author_fallback_email = Arr::get($feed, 'author_fallback_email');
        } else {
            $this->author_fallback_name = $defaults->get('default_author_fallback_name');
            $this->author_fallback_email = $defaults->get('default_author_fallback_email');
        }

        if (isset($feed['mappings'])) {
            // title
            $this->mappings['title'] = match (Arr::get($feed['mappings'], 'title_mode', 'default')) {
                'default' => $defaults->get('default_title'),
                'custom' => Arr::get($feed['mappings'], 'title'),
            };

            // summary
            $this->mappings['summary'] = match (Arr::get($feed['mappings'], 'summary_mode')) {
                'default' => $defaults->get('default_summary'),
                'custom' => Arr::get($feed['mappings'], 'summary'),
                default => null
            };

            // content
            $this->mappings['content'] = match (Arr::get($feed['mappings'], 'content_mode')) {
                'default' => $defaults->get('default_content'),
                'custom' => Arr::get($feed['mappings'], 'content'),
                default => null
            };

            // image
            if (! Arr::get($feed['mappings'], 'image_mode') || Arr::get($feed['mappings'], 'image_mode') === 'disabled'
            ) {
                $this->mappings['image'] = null;
            } else {
                $this->mappings['image'] = match (Arr::get($feed['mappings'], 'image_mode')) {
                    'default' => $defaults->get('default_image'),
                    'custom' => Arr::get($feed['mappings'], 'image')
                };

                // image dimensions
                $this->mappings['image_width'] = $defaults->get('default_image_width');
                $this->mappings['image_height'] = $defaults->get('default_image_height');
                if (Arr::get($feed['mappings'], 'image_dimensions_mode') === 'custom') {
                    $this->mappings['image_width'] = Arr::get($feed['mappings'], 'image_width');
                    $this->mappings['image_height'] = Arr::get($feed['mappings'], 'image_height');
                }
            }

            // author
            if (Arr::get($feed['mappings'], 'author_mode') === 'custom') {
                $this->mappings['author_type'] = Arr::get($feed['mappings'], 'author_type');
                $this->mappings['author_field'] = Arr::get($feed['mappings'], 'author_field');
                $this->mappings['author_name'] = Arr::get($feed['mappings'], 'author_name');
                $this->mappings['author_email'] = Arr::get($feed['mappings'], 'author_email');
            } elseif (Arr::get($feed['mappings'], 'author_mode') === 'default') {
                $this->mappings['author_type'] = $defaults->get('default_author_type');
                $this->mappings['author_field'] = $defaults->get('default_author_field');
                $this->mappings['author_name'] = $defaults->get('default_author_name');
                $this->mappings['author_email'] = $defaults->get('default_author_email');
            }
        }
    }

    public function hasRoute(string $route): bool
    {
        foreach (Feedamic::getFeedTypes() as $type) {
            if (Arr::get($this->routes, $type) === $route) {
                return true;
            }
        }

        return false;
    }

    public function getRoutes(): array
    {
        $routes = [];

        foreach (Feedamic::getFeedTypes() as $type) {
            if ($route = Arr::get($this->routes, $type)) {
                $routes[$type] = $route;
            }
        }

        return $routes;
    }

    public function getRouteForFeedType(string $type): ?string
    {
        return Arr::get($this->routes, $type, null);
    }

    public function getTitleMappings(): array
    {
        return $this->mappings['title'];
    }

    public function hasImage(): bool
    {
        return ! ($this->mappings['image'] === null);
    }

    public function hasSummary(): bool
    {
        return ! ($this->mappings['summary'] === null);
    }

    public function hasContent(): bool
    {
        return ! ($this->mappings['content'] === null);
    }

    public function hasAuthor(): bool
    {
        return ! ($this->mappings['author_type'] === null);
    }

    public function getImageMappings(): array
    {
        return $this->mappings['image'] ?? [];
    }

    public function getImageHeight(): ?int
    {
        if ($this->mappings['image_height']) {
            return (int) $this->mappings['image_height'];
        }

        return null;
    }

    public function getImageWidth(): ?int
    {
        if ($this->mappings['image_width']) {
            return (int) $this->mappings['image_width'];
        }

        return null;
    }

    public function getAuthorType(): ?string
    {
        return $this->mappings['author_type'];
    }

    public function getAuthor(): ?string
    {
        return $this->mappings['author_field'];
    }

    public function getAuthorName(): ?string
    {
        return $this->mappings['author_name'];
    }

    public function getAuthorEmail(): ?string
    {
        return $this->mappings['author_email'];
    }

    public function getSummaryMappings(): array
    {
        return $this->mappings['summary'] ?? [];
    }

    public function getContentMappings(): array
    {
        return $this->mappings['content'] ?? [];
    }

    public function getViewForRoute(string $route): ?string
    {
        foreach (Feedamic::getFeedTypes() as $type) {
            $configured = Arr::get($this->routes, $type, null);
            if ($configured === $route) {
                return Arr::get($this->routes, $type.'_view');
            }
        }

        return null;
    }

    public function makeUrlAbsolute(string $url): string
    {
        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        return Path::tidy(Str::ensureLeft($url, \Statamic\Facades\Site::current()->absoluteUrl()));
    }

    public function getCacheKey(string $route, string|Site $site): string
    {
        $type = null;
        foreach (Feedamic::getFeedTypes() as $feedType) {
            $configured = Arr::get($this->routes, $feedType, null);
            if ($configured === $route) {
                $type = $feedType;
            }
        }

        if (! $type) {
            throw new FeedNotConfiguredException(__('feedamic::exceptions.feed_not_configured', ['route' => $route]));
        }

        return implode('.', [
            config('feedamic.cache', 'feedamic'),
            $this->handle,
            $site instanceof Site ? $site->handle() : $site,
            $type,
        ]);
    }
}
