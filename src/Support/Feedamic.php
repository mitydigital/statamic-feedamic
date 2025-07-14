<?php

namespace MityDigital\Feedamic\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Sites\Site;

class Feedamic
{
    protected Collection $feeds;

    protected Collection $config;

    public function getRoutes(): array
    {
        $data = [];

        $config = $this->load();

        \Statamic\Facades\Site::all()->each(function (Site $site) use ($config, &$data) {
            $siteUrl = $site->url();
            if ($siteUrl === '/') {
                $siteUrl = config('app.url');
            }
            $uri = Uri::of($siteUrl);

            if ($uri->port()) {
                $domain = sprintf('%s://%s:%s',
                    $uri->scheme(),
                    $uri->host(),
                    $uri->port()
                );
            } else {
                $domain = sprintf('%s://%s',
                    $uri->scheme(),
                    $uri->host()
                );
            }

            // if this is the configured domain, treat it as the default
            if ($domain === config('app.url')) {
                $domain = 'default';
            }

            if (! Arr::exists($data, $domain)) {
                $data[$domain] = [];
            }

            // get the routes for the config
            foreach (Arr::get($config, 'feeds', []) as $feedConfig) {
                // is the feed applicable to this domain (either all, or specific sites)?
                if (Arr::get($feedConfig, 'sites') === 'all'
                    || in_array($site->handle(), Arr::get($feedConfig, 'sites_specific', []))
                ) {
                    foreach ($this->getFeedTypes() as $feedType) {
                        $route = Arr::get($feedConfig, 'routes.'.$feedType, null);

                        if ($route) {
                            // build the uri, remove duplicate // and get the path
                            $feedPath = (clone $uri)
                                ->withPath(preg_replace('/(\/+)/', '/', $uri->path().'/'.$route))
                                ->path();
                            $feedPath = Str::start($feedPath, '/');

                            // add it, if we need to
                            if (! in_array($feedType, $data[$domain])) {
                                $data[$domain][] = $feedPath;
                            }
                        }
                    }
                }
            }
        });

        // get the default domain
        $default = Arr::pull($data, 'default', []);

        return [
            'domains' => $data,
            'default' => $default,
        ];
    }

    public function load(): Collection
    {
        if (! isset($this->config)) {
            if (file_exists(self::getPath())) {
                $this->config = collect(YAML::file(self::getPath())->parse());
            } else {
                $this->config = collect([]);
            }
        }

        return $this->config;
    }

    public function getPath(): string
    {
        $path = config('feedamic.path');
        $filename = config('feedamic.filename');

        return $path.'/'.$filename.'.yaml';
    }

    public function getFeedTypes(): array
    {
        return ['atom', 'rss'];
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::find('feedamic::config');
    }

    public function save(array $payload): void
    {
        File::put(self::getPath(), YAML::dump($payload));
        unset($this->config);
    }

    public function getClassOfType(string $folder, string $requiredClass, ?callable $callback = null): array
    {
        if (! app('files')->exists($path = app_path($folder))) {
            return [];
        }

        return collect(app('files')->allFiles($path))
            ->map(function ($file) use ($folder, $callback, $requiredClass) {
                $class = $file->getBasename('.php');
                $fqcn = app()->getNamespace()."{$folder}\\{$class}";

                if ($callback) {
                    if ($callback($fqcn, $requiredClass)) {
                        return $fqcn;
                    }
                } elseif (is_subclass_of($fqcn, $requiredClass)) {
                    return $fqcn;
                }
            })
            ->filter()
            ->toArray();
    }

    public function getFeeds(): Collection
    {
        if (! isset($this->feeds)) {
            $this->getConfiguredFeeds();
        }

        return $this->feeds;
    }

    public function getFeedsForSite(string $handle)
    {
        if (! isset($this->feeds)) {
            $this->getConfiguredFeeds();
        }

        return $this->feeds
            ->filter(fn (array $config) => in_array($handle, $config['sites']));
    }

    protected function getConfiguredFeeds(): self
    {
        $this->load();

        $this->feeds = collect(Arr::get($this->config, 'feeds', []))
            ->mapWithKeys(function (array $feed) {
                $feedConfig = [
                    'handle' => Arr::get($feed, 'handle'),
                    'title' => Arr::get($feed, 'title'),
                    'description' => Arr::get($feed, 'description'),
                    'sites' => [],
                    'routes' => [],
                    'limit' => null,
                    'alt_url' => Arr::get($feed, 'alt_url'),
                    'copyright' => '',
                    'mappings' => [],
                    'model' => Arr::get($feed, 'model'),
                ];

                // update limit
                if (Arr::get($feed, 'show', 'all') === 'limit') {
                    $feedConfig['limit'] = Arr::get($feed, 'limit');
                }

                // update site config
                if (Arr::get($feed, 'sites') === 'all') {
                    $feedConfig['sites'] =
                        \Statamic\Facades\Site::all()->map(fn (Site $site) => $site->handle())->values()->toArray();
                } else {
                    $feedConfig['sites'] = Arr::get($feed, 'sites_specific', []);
                }

                // update routes
                $feedConfig['routes'] = [];
                foreach ($this->getFeedTypes() as $feedType) {
                    if ($route = Arr::get($feed['routes'], $feedType, null)) {
                        $feedConfig['routes'][$feedType] = $route;
                        $view = 'mitydigital/feedamic::'.$feedType;
                        if ($override = Arr::get($feed['routes'], $feedType.'_view')) {
                            $view = $override;
                        }
                        $feedConfig['routes'][$feedType.'_view'] = $view;
                    }
                }

                // copyright
                $feedConfig['copyright'] = match (Arr::get($feed, 'copyright_mode')) {
                    'default' => Arr::get($this->config, 'default_copyright'),
                    'custom' => Arr::get($feed, 'copyright'),
                    'disabled' => null
                };

                // title
                $feedConfig['mappings']['title'] = match (Arr::get($feed['mappings'], 'title_mode')) {
                    'default' => Arr::get($this->config, 'default_title'),
                    'custom' => Arr::get($feed['mappings'], 'title'),
                };

                // summary
                $feedConfig['mappings']['summary'] = match (Arr::get($feed['mappings'], 'summary_mode')) {
                    'default' => Arr::get($this->config, 'default_summary'),
                    'custom' => Arr::get($feed['mappings'], 'summary'),
                    'disabled' => null
                };

                // image
                if (Arr::get($feed['mappings'], 'image_mode') === 'disabled') {
                    $feedConfig['mappings']['image'] = null;
                } else {
                    $feedConfig['mappings']['image'] = match (Arr::get($feed['mappings'], 'image_mode')) {
                        'default' => Arr::get($this->config, 'default_image'),
                        'custom' => Arr::get($feed['mappings'], 'image')
                    };

                    // image dimensions
                    if (Arr::get($feed['mappings'], 'image_dimensions_mode') === 'custom') {
                        $feedConfig['mappings']['image_width'] = Arr::get($feed['mappings'], 'image_width');
                        $feedConfig['mappings']['image_height'] = Arr::get($feed['mappings'], 'image_height');
                    } elseif (Arr::get($feed['mappings'], 'image_dimensions_mode') === 'default') {
                        $feedConfig['mappings']['image_width'] = Arr::get($this->config, 'default_image_width');
                        $feedConfig['mappings']['image_height'] = Arr::get($this->config, 'default_image_height');
                    }
                }

                // author
                $feedConfig['mappings']['author_type'] = null;
                $feedConfig['mappings']['author_name'] = null;
                $feedConfig['mappings']['author_email'] = null;
                if (Arr::get($feed['mappings'], 'author_mode') === 'custom') {
                    $feedConfig['mappings']['author_type'] = Arr::get($feed['mappings'], 'author_type');
                    $feedConfig['mappings']['author_name'] = Arr::get($feed['mappings'], 'author_name');
                    $feedConfig['mappings']['author_email'] = Arr::get($feed['mappings'], 'author_email');
                } elseif (Arr::get($feed['mappings'], 'author_mode') === 'default') {
                    $feedConfig['mappings']['author_type'] = Arr::get($this->config, 'default_author_type');
                    $feedConfig['mappings']['author_name'] = Arr::get($this->config, 'default_author_name');
                    $feedConfig['mappings']['author_email'] = Arr::get($this->config, 'default_author_email');
                }

                return [$feedConfig['handle'] => $feedConfig];
            });

        return $this;
    }
}
