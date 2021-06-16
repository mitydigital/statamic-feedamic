<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('statamic.rss.title') }}</title>
        <description>{{ config('statamic.rss.description') }}</description>
        <link>{{ config('app.url') }}</link>
        <atom:link href="{{ config('app.url') }}{{ config('statamic.rss.routes.rss') }}" rel="self" type="application/rss+xml" />
        @if($updated)<lastBuildDate>{{ $updated->toRfc822String() }}</lastBuildDate>@endif

        @foreach ($entries as $entry)<item>
            <title><![CDATA[{{ $entry->title }}]]></title>
            <link>{{ $entry->uri }}</link>
            <guid isPermaLink="true">{{ $entry->uri }}</guid>
            <pubDate>{{ $entry->published->toRfc822String() }}</pubDate>
            @if ($entry->summary)<description><![CDATA[{{ $entry->summary }}]]></description>@endif

            @if (config('statamic.rss.author.email'))<author>{{ $entry->author->email() }}</author>@endif

        </item>@endforeach

    </channel>
</rss>