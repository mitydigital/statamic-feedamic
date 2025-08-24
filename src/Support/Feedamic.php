<?php

namespace MityDigital\Feedamic\Support;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use MityDigital\Feedamic\Abstracts\AbstractFeedamicEntry;
use MityDigital\Feedamic\Exceptions\CollectionMissingRouteException;
use MityDigital\Feedamic\Exceptions\InconsistentSortFieldException;
use MityDigital\Feedamic\Exceptions\ModifierCallbackException;
use MityDigital\Feedamic\Exceptions\ProcessorCallbackException;
use MityDigital\Feedamic\Exceptions\ViewNotFoundException;
use MityDigital\Feedamic\Models\FeedamicConfig;
use MityDigital\Feedamic\Models\FeedamicEntry;
use ReflectionFunction;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Entry;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Sites\Site;
use Statamic\Statamic;
use Stringy\StaticStringy;
use XMLReader;
use XMLWriter;

class Feedamic
{
    protected Collection $feeds;

    protected Collection $config;

    protected Collection $modifiers; // modify a FeedamicEntry value

    protected Collection $processors; // process an Entry value

    public function __construct()
    {
        $this->modifiers = collect();
        $this->processors = collect();
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::find('feedamic::settings');
    }

    public function save(array $payload): void
    {
        File::put(self::getPath(), YAML::dump($payload));
        unset($this->config);
        unset($this->feeds);
    }

    public function getPath(): string
    {
        $path = config('feedamic.path');
        $filename = config('feedamic.filename');

        return $path.'/'.$filename.'.yaml';
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

    public function getFeedsForSite(string $handle)
    {
        if (! isset($this->feeds)) {
            $this->getConfiguredFeeds();
        }

        return $this->feeds
            ->filter(fn (FeedamicConfig $config) => in_array($handle, $config->sites));
    }

    public function getConfig(string $path, string $site): ?FeedamicConfig
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

            if (! $collection->route(\Statamic\Facades\Site::current()->handle())) {
                throw new CollectionMissingRouteException(__('feedamic::exceptions.collection_missing_route', [
                    'collection' => $collection->title,
                ]));
            }

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

            if ($collection->dated()) {
                $now = Carbon::now();
                // collection date behaviour
                if ($collection->futureDateBehavior() !== 'public') {
                    $query->whereDate('date', '<=', $now->format('Y-m-d'))
                        ->whereTime('date', '<=', $now->format('H:i:s'));
                }

                if ($collection->pastDateBehavior() !== 'public') {
                    $query->whereDate('date', '>=', $now->format('Y-m-d'))
                        ->whereTime('date', '>=', $now->format('H:i:s'));
                }
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

        $entries = LazyCollection::make(function () use ($lazyDataSources, $sortField, $config) {
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
                    $currentSortValue = match ($sortField) {
                        'date' => $data['current']->date(),
                        'order' => $data['current']->order(),
                        default => $data['current']->get($sortField),
                    };
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

                yield new FeedamicEntry($maxValue, $config);

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

    public function clearCache(?array $handles = null, ?array $sites = null, ?string $collection = null): array
    {
        $this->getConfiguredFeeds();

        if (! $handles) {
            $handles = $this->feeds->keys()->toArray();
        }

        if (! $sites) {
            $sites = \Statamic\Facades\Site::all()->pluck('handle')->toArray();
        }

        $cleared = [];
        foreach ($handles as $handle) {
            // get the feed config
            $config = $this->feeds->get($handle);

            // if there is no collection, or this feed uses that collection
            if (! $collection || in_array($collection, $config->collections)) {
                foreach ($config->sites as $site) {
                    if (in_array($site, $sites)) {
                        foreach ($config->getRoutes() as $route) {
                            $key = $config->getCacheKey($route, $site);
                            Cache::forget($key);
                            $cleared[] = $key;
                        }
                    }
                }
            }
        }

        return $cleared;
    }

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

    public function getFeedTypes(): array
    {
        return ['atom', 'rss'];
    }

    public function processor(
        string $fieldHandle,
        Closure $processor,
        ?Closure $when = null,
        ?array $feeds = null
    ): void {
        foreach (['modifier' => $processor, 'when' => $when] as $callback => $closure) {
            if (! $closure) {
                continue;
            }

            $reflection = new ReflectionFunction($closure);
            $parameters = $reflection->getParameters();

            // 0 - AbstractFeedamicEntry
            $entry = Arr::get($parameters, 0);
            if (! $entry || ($entry->getType() && $entry->getType()->getName() !== AbstractFeedamicEntry::class)) {
                throw new ProcessorCallbackException(__('feedamic::exceptions.processor_callback', [
                    'handle' => $fieldHandle,
                    'callback' => $callback,
                    'argument' => 'MityDigital\Feedamic\AbstractFeedamicEntry $entry',
                ]));
            }

            // 1 - $value, whatever you want it to be, just needs to exist
            if (! array_key_exists(1, $parameters)) {
                throw new ProcessorCallbackException(__('feedamic::exceptions.processor_callback', [
                    'handle' => $fieldHandle,
                    'callback' => $callback,
                    'argument' => '$value',
                ]));
            }
        }

        $this->processors->add([
            'feeds' => $feeds,
            'handle' => $fieldHandle,
            'processor' => $processor,
            'when' => $when,
        ]);
    }

    public function removeProcessor(string $fieldHandle): void
    {
        $this->processors =
            $this->processors->reject(fn (array $processor) => Arr::get($processor, 'handle') === $fieldHandle);
    }

    public function getProcessor(AbstractFeedamicEntry $feedamicEntry, string $fieldHandle, mixed $value): ?Closure
    {
        $processor = $this->processors
            ->first(function (array $processor) use ($feedamicEntry, $value, $fieldHandle) {
                if (Arr::get($processor, 'handle') === $fieldHandle) {
                    if ($processor['feeds'] === null
                        || in_array($feedamicEntry->config()->handle, $processor['feeds'])
                    ) {
                        if ($when = $processor['when']) {
                            if (! $when($feedamicEntry, $value)) {
                                return false;
                            }
                        }

                        return true;
                    }
                }

                return false;
            });

        return $processor ? $processor['processor'] : null;
    }

    public function modify(
        string $feedamicEntryProperty,
        Closure $modifier,
        ?Closure $when = null,
        ?array $feeds = null
    ): void {
        foreach (['modifier' => $modifier, 'when' => $when] as $callback => $closure) {
            if (! $closure) {
                continue;
            }

            $reflection = new ReflectionFunction($closure);
            $parameters = $reflection->getParameters();

            // 0 - AbstractFeedamicEntry
            $entry = Arr::get($parameters, 0);
            if (! $entry || ($entry->getType() && $entry->getType()->getName() !== AbstractFeedamicEntry::class)) {
                throw new ModifierCallbackException(__('feedamic::exceptions.modifier_callback', [
                    'property' => $feedamicEntryProperty,
                    'callback' => $callback,
                    'argument' => 'MityDigital\Feedamic\AbstractFeedamicEntry $entry',
                ]));
            }

            // 1 - $value, whatever you want it to be, just needs to exist
            if (! array_key_exists(1, $parameters)) {
                throw new ModifierCallbackException(__('feedamic::exceptions.modifier_callback', [
                    'property' => $feedamicEntryProperty,
                    'callback' => $callback,
                    'argument' => '$value',
                ]));
            }
        }

        $this->modifiers->add([
            'feeds' => $feeds,
            'modifier' => $modifier,
            'property' => $feedamicEntryProperty,
            'when' => $when,
        ]);
    }

    public function removeModifier(string $feedamicEntryProperty): void
    {
        $this->modifiers =
            $this->modifiers->reject(fn (array $modifier) => Arr::get($modifier, 'property')
                === $feedamicEntryProperty);
    }

    public function getModifier(
        AbstractFeedamicEntry $feedamicEntry,
        string $feedamicEntryProperty,
        mixed $value
    ): ?Closure {
        $modifier = $this->modifiers
            ->first(function (array $modifier) use ($feedamicEntry, $value, $feedamicEntryProperty) {
                if (Arr::get($modifier, 'property') === $feedamicEntryProperty) {
                    if ($modifier['feeds'] === null || in_array($feedamicEntry->config()->handle, $modifier['feeds'])) {
                        if ($when = $modifier['when']) {
                            if (! $when($feedamicEntry, $value)) {
                                return false;
                            }
                        }

                        return true;
                    }
                }

                return false;
            });

        return $modifier ? $modifier['modifier'] : null;
    }

    public function render(FeedamicConfig $config, string $route): string
    {
        $view = $config->getViewForRoute($route);
        if (! View::exists($view)) {
            throw new ViewNotFoundException(__('feedamic::exceptions.view_not_found', [
                'view' => $view,
            ]));
        }

        $cacheKey = $config->getCacheKey($route, \Statamic\Facades\Site::current());

        // do we have a cached version?
        if (config('feedamic.cache_enabled', true) && Cache::has($cacheKey)) {
            $feed = Cache::get($cacheKey);
        } else {
            // it could be a while...
            set_time_limit(0);

            // return the view
            $xml = view($view, [
                'id' => request()->url(),
                'config' => $config,
                'entries' => \MityDigital\Feedamic\Facades\Feedamic::getEntries($config),
                'site' => \Statamic\Facades\Site::current(),
                'updated' => Carbon::now(),
                'url' => request()->url(),
            ])->render();

            if (class_exists(XMLReader::class) && class_exists(XMLWriter::class)) {
                // output
                ob_start();

                $reader = new XMLReader;
                $reader->XML($xml, 'UTF-8', LIBXML_NOBLANKS);

                $writer = new XMLWriter;
                $writer->openURI('php://output');
                $writer->setIndent(true);
                $writer->setIndentString('    ');
                $writer->startDocument('1.0', 'UTF-8');

                while ($reader->read()) {
                    switch ($reader->nodeType) {
                        case XMLReader::ELEMENT:
                            $writer->startElement($reader->name);
                            if ($reader->hasAttributes) {
                                while ($reader->moveToNextAttribute()) {
                                    $writer->writeAttribute($reader->name, $reader->value);
                                }
                                $reader->moveToElement();
                            }
                            if ($reader->isEmptyElement) {
                                $writer->endElement();
                            }
                            break;
                        case XMLReader::TEXT:
                            $writer->text(trim($reader->value));
                            break;
                        case XMLReader::CDATA:
                            $writer->writeCdata(trim($reader->value));
                            break;
                        case XMLReader::END_ELEMENT:
                            $writer->endElement();
                            break;
                    }
                }

                $writer->endDocument();
                $reader->close();

                $feed = ob_get_clean();
            } else {
                // xml processing not available
                $feed = $xml;
            }

            if (config('feedamic.cache_enabled', true)) {
                // store in the cache
                Cache::put($cacheKey, $feed);
            }
        }

        return $feed;
    }

    public function version(): string
    {
        return Addon::get('mitydigital/feedamic')->version();
    }

    public function includeCpRoutes(): bool
    {
        try {
            $version = Statamic::version();
        } catch (Exception $e) {
            return false;
        }

        if (Str::before($version, '.') === '5') {
            return true;
        }

        return false;
    }
}
