<?php

namespace MityDigital\Feedamic\Models;

use Illuminate\Support\Arr;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Sites\Site;

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

    public array $mappings = [
        'title' => '',
        'summary' => '',
        'content' => '',
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

    public function __construct($feed, $defaults)
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
            if ($route = Arr::get($feed['routes'], $feedType, null)) {
                $this->routes[$feedType] = $route;
                $view = 'mitydigital/feedamic::'.$feedType;
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

        // title
        $this->mappings['title'] = match (Arr::get($feed['mappings'], 'title_mode')) {
            'default' => Arr::get($defaults, 'default_title'),
            'custom' => Arr::get($feed['mappings'], 'title'),
        };

        // summary
        $this->mappings['summary'] = match (Arr::get($feed['mappings'], 'summary_mode')) {
            'default' => Arr::get($defaults, 'default_summary'),
            'custom' => Arr::get($feed['mappings'], 'summary'),
            default => null
        };

        // content
        $this->mappings['content'] = match (Arr::get($feed['mappings'], 'content_mode')) {
            'default' => Arr::get($defaults, 'default_content'),
            'custom' => Arr::get($feed['mappings'], 'content'),
            default => null
        };

        // image
        if (Arr::get($feed['mappings'], 'image_mode') === 'disabled') {
            $this->mappings['image'] = null;
        } else {
            $this->mappings['image'] = match (Arr::get($feed['mappings'], 'image_mode')) {
                'default' => Arr::get($defaults, 'default_image'),
                'custom' => Arr::get($feed['mappings'], 'image')
            };

            // image dimensions
            if (Arr::get($feed['mappings'], 'image_dimensions_mode') === 'custom') {
                $this->mappings['image_width'] = Arr::get($feed['mappings'], 'image_width');
                $this->mappings['image_height'] = Arr::get($feed['mappings'], 'image_height');
            } elseif (Arr::get($feed['mappings'], 'image_dimensions_mode') === 'default') {
                $this->mappings['image_width'] = Arr::get($defaults, 'default_image_width');
                $this->mappings['image_height'] = Arr::get($defaults, 'default_image_height');
            }
        }

        // author
        if (Arr::get($feed['mappings'], 'author_mode') === 'custom') {
            $this->mappings['author_type'] = Arr::get($feed['mappings'], 'author_type');
            $this->mappings['author_field'] = Arr::get($feed['mappings'], 'author_field');
            $this->mappings['author_name'] = Arr::get($feed['mappings'], 'author_name');
            $this->mappings['author_email'] = Arr::get($feed['mappings'], 'author_email');
        } elseif (Arr::get($feed['mappings'], 'author_mode') === 'default') {
            $this->mappings['author_type'] = Arr::get($defaults, 'default_author_type');
            $this->mappings['author_field'] = Arr::get($defaults, 'default_author_field');
            $this->mappings['author_name'] = Arr::get($defaults, 'default_author_name');
            $this->mappings['author_email'] = Arr::get($defaults, 'default_author_email');
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

    public function getImageHeight(): int
    {
        return (int) $this->mappings['image_height'];
    }

    public function getImageWidth(): int
    {
        return (int) $this->mappings['image_width'];
    }

    public function getAuthorType(): string
    {
        return $this->mappings['author_type'];
    }

    public function getAuthor(): string
    {
        return $this->mappings['author_field'];
    }

    public function getAuthorName(): string
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
}
