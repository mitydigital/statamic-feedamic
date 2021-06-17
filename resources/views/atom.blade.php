<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ config('statamic.rss.language') }}">
    <id>{{ $id }}</id>
    <title type="text">{!! config('statamic.rss.title') !!}</title>
    <subtitle type="text">{!! config('statamic.rss.description') !!}</subtitle>
    <link rel="alternate" type="text/html" hreflang="en" href="{{ config('app.url') }}"/>
    <link rel="self" type="application/atom+xml" xmlns="http://www.w3.org/2005/Atom" href="{{ config('app.url') }}{{ config('statamic.rss.routes.atom') }}"/>
    @if ($updated)<updated>{{ $updated->toRfc3339String() }}</updated>@endif

    @if (config('statamic.rss.copyright'))<rights>{!! config('statamic.rss.copyright') !!}</rights>@endif

    <generator uri="https://github.com/mitydigital/statamic-rss-feed" version="1.3">Atom and RSS Feed for Statamic 3</generator>

    @foreach ($entries as $entry)
<entry>
        <title type="html">{!! $entry->title() !!}</title>
        <link href="{{ $entry->uri }}"/>
        <id>{{ $entry->uri }}</id>
        <published>{{ $entry->published->toRfc3339String() }}</published>
        <updated>{{ $entry->updated->toRfc3339String() }}</updated>
        @if ($entry->summary)<summary>{!! $entry->summary() !!}</summary>@endif

        <content src="{{ $entry->uri }}" type="text/html"></content>
        @if ($entry->author)<author>
            <name>{{ $entry->author->name() }}</name>@if (config('statamic.rss.author.email'))<email>{{ $entry->author->email() }}</email>@endif

        </author>@endif

    </entry>
    @endforeach

</feed>
