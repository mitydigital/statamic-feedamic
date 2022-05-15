<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ config('feedamic.language') }}">
    <id>{{ $id }}</id>
    <title type="text">{!! config('feedamic.title') !!}</title>
    <subtitle type="text">{!! config('feedamic.description') !!}</subtitle>
    <link rel="alternate" type="text/html" hreflang="en" href="{{ config('app.url') }}"/>
    <link rel="self" type="application/atom+xml" xmlns="http://www.w3.org/2005/Atom" href="{{ config('app.url') }}{{ config('feedamic.routes.atom') }}"/>
    @if ($updated)<updated>{{ $updated->toRfc3339String() }}</updated>@endif

    @if (config('feedamic.copyright'))<rights>{!! config('feedamic.copyright') !!}</rights>@endif

    <generator uri="https://github.com/mitydigital/feedamic" version="2.0">Feedamic: the Atom and RSS Feed generator for Statamic</generator>

    @foreach ($entries as $entry)
<entry>
        <title type="html">{!! $entry->title() !!}</title>
        <link href="{{ $entry->uri }}"/>
        <id>{{ $entry->uri }}</id>
        <published>{{ $entry->published->toRfc3339String() }}</published>
        <updated>{{ $entry->updated->toRfc3339String() }}</updated>
        @if ($entry->hasSummaryOrImage())<summary type="html">{!! $entry->summary() !!}</summary>@endif

        <content src="{{ $entry->uri }}" type="text/html"></content>
        @if ($entry->author)<author>
            <name>{{ $entry->author->name() }}</name>@if (config('feedamic.author.email'))<email>{{ $entry->author->email() }}</email>@endif

        </author>@endif

    </entry>
    @endforeach

</feed>
