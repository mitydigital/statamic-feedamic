<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{!! $config->title !!}</title>
        <description>{!! $config->description !!}</description>
        @if ($config->alt_url)
        <link>{{ $config->alt_url }}</link>
        @endif
        <lastBuildDate>{{ $updated->toRfc2822String() }}</lastBuildDate>
        <language>{{ $site->lang }}</language>
        @if ($config->copyright)
            <copyright>{!! $config->copyright !!}</copyright>
        @endif

        <generator>{{ __('feedamic::feeds.generator') }}</generator>

        @foreach ($entries as $entry)
            <item>
                <title><![CDATA[{!! $entry->title() !!}]]></title>
                <link>{{ $entry->url() }}</link>
                <guid isPermaLink="true">{{ $entry->url() }}</guid>
                <pubDate>{{ $entry->getUpdatedAt()->toRfc822String() }}</pubDate>
                @if ($entry->hasSummary() && $entry->hasImage())
                <description><![CDATA[{!! $entry->summary() !!}]]></description>
                @elseif ($entry->hasSummary())
                    <description><![CDATA[{!! $entry->summary() !!}]]></description>
                @endif

                @if ($entry->hasAuthor())
                    @if ($email = $entry->author()->email())
                    <author>{{ $email }} ({{ $entry->author()->name() }})</author>
                    @else
                    <author>{{ $entry->author()->name() }}</author>
                    @endif
                @else
                    @if ($email = $config->author_fallback_email)
                    <author>{{ $email }} ({{ $config->author_fallback_name }})</author>
                    @else
                    <author>{{ $config->author_fallback_name }}</author>
                    @endif
                @endif

            </item>
        @endforeach

    </channel>
</rss>