<?php

namespace MityDigital\Feedamic\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use MityDigital\Feedamic\Exceptions\ViewNotFoundException;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Facades\Site;
use XMLReader;
use XMLWriter;

class FeedamicController
{
    public function __invoke()
    {
        $route = Str::remove(Site::current()->absoluteUrl(), request()->url());

        $config = Feedamic::getConfig(
            path: $route,
            site: Site::current()->handle()
        );

        // if there's no config, abort!
        if (! $config) {
            abort(404);
        }

        $view = $config->getViewForRoute($route);
        if (! View::exists($view)) {
            throw new ViewNotFoundException(__('feedamic::exceptions.view_not_found', [
                'view' => $view,
            ]));
        }

        $cacheKey = $config->getCacheKey(Site::current());

        // do we have a cached version?
        if (Cache::has($cacheKey)) {
            $feed = Cache::get($cacheKey);
        } else {
            // it could be a while...
            set_time_limit(0);

            // return the view
            $xml = view($view, [
                'id' => request()->url(),
                'config' => $config,
                'entries' => Feedamic::getEntries($config),
                'site' => Site::current(),
                'updated' => Carbon::now(),
                'url' => request()->url(),
            ])->render();

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
                    case XMLReader::CDATA:
                        $writer->text($reader->value);
                        break;
                    case XMLReader::END_ELEMENT:
                        $writer->endElement();
                        break;
                }
            }

            $writer->endDocument();
            $reader->close();

            $feed = ob_get_clean();

            // store in the cache
            Cache::put($cacheKey, $feed);
        }

        return Response::make($feed, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
