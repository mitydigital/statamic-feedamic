<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('statamic.rss.title') }}</title>
        <description>{!! config('statamic.rss.description') !!}</description>
        <link>{{ config('app.url') }}</link>
        <atom:link href="{{ config('app.url') }}{{ config('statamic.rss.routes.rss') }}" rel="self" type="application/rss+xml" />
        @if($updated)<lastBuildDate>{{ $updated->toRfc822String() }}</lastBuildDate>@endif

        <language>{{ config('statamic.rss.language') }}</language>
        @if (config('statamic.rss.copyright'))<copyright>{!! config('statamic.rss.copyright') !!}</copyright>@endif

        <generator>Atom and RSS Feed for Statamic 3</generator>

        @foreach ($entries as $entry)<item>
            <title><![CDATA[{!! html_entity_decode($entry->title(false)) !!}]]></title>
            <link>{{ $entry->uri }}</link>
            <guid isPermaLink="true">{{ $entry->uri }}</guid>
            <pubDate>{{ $entry->published->toRfc822String() }}</pubDate>
            @if ($entry->hasSummaryOrImage())<description><![CDATA[{!! $entry->summary(false) !!}]]></description>@endif

            @if (config('statamic.rss.author.email'))<author>{{ $entry->author->email() }}</author>@endif

        </item>@endforeach

    </channel>
</rss>
