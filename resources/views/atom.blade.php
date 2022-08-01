<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ $language }}">
    <id>{{ $id }}</id>
    <title type="text">{!! $title !!}</title>
    @if($description)
        <subtitle type="text">{!! $description !!}</subtitle>
    @endif
    <link rel="alternate" type="text/html" hreflang="en" href="{{ $alt_url }}"/>
    <link rel="self" type="application/atom+xml" xmlns="http://www.w3.org/2005/Atom" href="{{ $href }}"/>
    @if ($updated)
        <updated>{{ $updated->toRfc3339String() }}</updated>
    @endif

    @if ($copyright)
        <rights>{!! $copyright !!}</rights>
    @endif

    <generator uri="https://github.com/mitydigital/feedamic" version="2.2">
        Feedamic: the Atom and RSS Feed generator for Statamic
    </generator>

    @foreach ($entries as $entry)
        <entry>
            <title type="html">{!! $entry->title() !!}</title>
            <link href="{{ $entry->uri }}"/>
            <id>{{ $entry->uri }}</id>
            <published>{{ $entry->published->toRfc3339String() }}</published>
            <updated>{{ $entry->updated->toRfc3339String() }}</updated>
            @if ($entry->hasSummaryOrImage())
                <summary type="html">{!! $entry->summary() !!}</summary>
            @endif

            <content src="{{ $entry->uri }}" type="text/html"></content>
            @if ($entry->author)
                <author>
                    <name>{{ $entry->author->name() }}</name>@if ($author_email && $entry->author->email())
                        <email>{{ $entry->author->email() }}</email>
                    @endif

                </author>
            @endif

        </entry>
    @endforeach

</feed>
