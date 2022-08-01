<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{!! $title !!}</title>
        <description>{!! $description !!}</description>
        <link>{{ $alt_url }}</link>
        <atom:link href="{{ $href }}" rel="self" type="application/rss+xml"/>
        @if($updated)
            <lastBuildDate>{{ $updated->toRfc822String() }}</lastBuildDate>
        @endif

        <language>{{ $language }}</language>
        @if ($copyright)
            <copyright>{!! $copyright !!}</copyright>
        @endif

        <generator>Feedamic: the Atom and RSS Feed generator for Statamic</generator>

        @foreach ($entries as $entry)
            <item>
                <title><![CDATA[{!! html_entity_decode($entry->title(false)) !!}]]></title>
                <link>{{ $entry->uri }}</link>
                <guid isPermaLink="true">{{ $entry->uri }}</guid>
                <pubDate>{{ $entry->published->toRfc822String() }}</pubDate>
                @if ($entry->hasSummaryOrImage())
                    <description><![CDATA[{!! $entry->summary(false) !!}]]></description>
                @endif

                @if ($author_email)
                    <author>{{ $entry->author->email() }}</author>
                @endif

            </item>
        @endforeach

    </channel>
</rss>
