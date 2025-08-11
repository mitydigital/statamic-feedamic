<?php

namespace MityDigital\Feedamic\Support;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use MityDigital\Feedamic\Exceptions\InconsistentSortFieldException;
use MityDigital\Feedamic\Models\FeedamicConfig;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Sites\Site;
use Stringy\StaticStringy;

class Feedamic
{
    protected Collection $feeds;

    protected Collection $config;

    public function getRoutes(): array
    {
        $data = [];

        $config = $this->getConfiguredFeeds();

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
            $config->feeds->each(function (FeedamicConfig $config) use ($site, $uri, &$data, $domain) {
                if (in_array($site->handle(), $config->sites)) {
                    foreach ($this->getFeedTypes() as $feedType) {
                        $route = $config->getRouteForFeedType($feedType);

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
            });
        });

        // get the default domain
        $default = Arr::pull($data, 'default', []);

        return [
            'domains' => $data,
            'default' => $default,
        ];
    }

    protected function getConfiguredFeeds(): self
    {
        $this->load();

        $this->feeds = collect(Arr::get($this->config, 'feeds', []))
            ->mapWithKeys(function (array $feed) {
                return [Arr::get($feed, 'handle') => new FeedamicConfig($feed, $this->config)];
            });

        return $this;
    }

    public function load($refresh = false): Collection
    {
        if (! isset($this->config) || $refresh === true) {
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
        unset($this->feeds);
    }

    public function getClassOfType(string $abstractClass): array
    {
        if (! app('files')->exists($path = app_path())) {
            return [];
        }

        return collect(\Illuminate\Support\Facades\File::allFiles(app_path()))
            ->map(function ($file) {
                $path = $file->getRealPath();

                // Turn file path into fully qualified class name
                $class = 'App\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($path, app_path().DIRECTORY_SEPARATOR)
                );

                return $class;
            })
            ->filter(function ($class) use ($abstractClass) {
                return class_exists($class)
                    && is_subclass_of($class, $abstractClass);
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
            ->filter(fn (FeedamicConfig $config) => in_array($handle, $config->sites));
    }

    public function getConfig(string $path, string $site)
    {
        if (! isset($this->feeds)) {
            $this->getConfiguredFeeds();
        }

        $path = Str::start($path, '/');

        return $this->feeds
            ->first(fn (FeedamicConfig $config) => in_array($site, $config->sites) && $config->hasRoute($path));
    }

    public function getEntries(FeedamicConfig $config)
    {
        $sortField = null;

        $limit = $config->limit;

        $lazyDataSources = [];
        foreach ($config->collections as $collection) {
            $collection = \Statamic\Facades\Collection::find($collection);

            $thisSortField = $collection->sortField() ? $collection->sortField() : null;
            if ($collection->dated()) {
                $thisSortField = 'date';
            }
            if (! $sortField) {
                $sortField = $thisSortField; // just set the sort field
            } else {
                if ($sortField !== $thisSortField) {
                    throw new InconsistentSortFieldException(__('feedamic::exceptions.inconsistent_sort_field', [
                        'collection' => $collection->title,
                        'thisField' => $thisSortField,
                        'field' => $sortField,
                    ]));
                }
            }

            $query = Entry::query()
                ->where('site', \Statamic\Facades\Site::current()->handle())
                ->where('collection', $collection->handle())
                ->where('published', true)
                ->orderBy($sortField, 'desc');

            // collection date behaviour
            if ($collection->futureDateBehavior() === 'private') {
                $query->where('date', '<=', now());
            }

            if ($collection->pastDateBehavior() === 'private') {
                $query->where('date', '>=', now());
            }

            // filter by taxonomy terms
            foreach ($config->taxonomies as $termsConfig) {
                $logic = strtolower(Arr::get($termsConfig, 'logic', 'and'));
                switch ($logic) {
                    case 'and':
                        foreach (Arr::get($termsConfig, 'terms', []) as $term) {
                            $query = $query->whereTaxonomy($term);
                        }
                        break;
                    case 'or':
                        // OR LOGIC
                        $query = $query->whereTaxonomyIn(Arr::get($termsConfig, 'terms', []));
                        break;
                }
            }

            // do we have a scope?
            // if so, let's apply it
            if ($scope = $config->scope) {
                app($scope)->apply($query, $config);
            }

            $lazyDataSources[] = LazyCollection::make(function () use ($query, $limit) {
                $query = $query->lazy();
                if ($limit) {
                    $query = $query->take($limit);
                }

                foreach ($query as $entry) {
                    yield $entry;
                }
            });
        }

        $entries = LazyCollection::make(function () use ($lazyDataSources, $sortField) {
            $iterators = [];

            foreach ($lazyDataSources as $key => $collection) {
                $iterator = $collection->getIterator();
                $iterators[$key] = [
                    'iterator' => $iterator,
                    'current' => $iterator->valid() ? $iterator->current() : null,
                ];
            }

            while (true) {
                $maxKey = null;
                $maxValue = null;
                $maxSortValue = null;

                foreach ($iterators as $key => &$data) {
                    if ($data['current'] === null) {
                        continue;
                    }

                    // get the sort value, and if a Carbon object (i.e. "date"), compare from the timestamp
                    $currentSortValue =
                        $sortField === 'date' ? $data['current']->date() : $data['current']->get($sortField);
                    if ($currentSortValue instanceof Carbon) {
                        $currentSortValue = $currentSortValue->timestamp;
                    }

                    if ($currentSortValue === null) {
                        continue;
                    }

                    if ($maxValue === null || $currentSortValue > $maxSortValue) {
                        $maxKey = $key;
                        $maxValue = $data['current'];
                        $maxSortValue = $currentSortValue;
                    }
                }

                if ($maxKey === null) {
                    break;
                }

                yield $maxValue;

                $iterators[$maxKey]['iterator']->next();
                $iterators[$maxKey]['current'] =
                    $iterators[$maxKey]['iterator']->valid() ? $iterators[$maxKey]['iterator']->current() : null;
            }
        });

        // we have a limit, so only take those
        if ($limit) {
            return $entries->take($limit);
        }

        return $entries;
    }

    public function svg(string $name, ?string $attrs = null): string
    {
        if ($attrs) {
            $attrs = " class=\"{$attrs}\"";
        }

        $svg = StaticStringy::collapseWhitespace(
            File::get(Path::tidy(__DIR__."/../../resources/svg/{$name}.svg"))
        );

        return str_replace('<svg', sprintf('<svg%s', $attrs), $svg);
    }
}
