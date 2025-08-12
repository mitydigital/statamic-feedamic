<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ $site->lang }}">
    <id>{{ $id }}</id>
    <title type="text">{!! $config->title !!}</title>
    @if($config->description)
    <subtitle type="text">{!! $config->description !!}</subtitle>
    @endif
    @if ($config->alt_url)
    <link rel="alternate" type="text/html" hreflang="en" href="{{ $config->alt_url }}"/>
    @endif
    @if ($url)
    <link rel="self" type="application/atom+xml" xmlns="http://www.w3.org/2005/Atom" href="{{ $url }}"/>
    @endif
    <updated>{{ $updated->toRfc3339String() }}</updated>
    @if ($config->copyright)
        <rights>{!! $config->copyright !!}</rights>
    @endif

    <generator uri="https://github.com/mitydigital/feedamic" version="2.2">
        Feedamic: the Atom and RSS Feed generator for Statamic
    </generator>


    @foreach ($entries as $entry)
<entry>
        <title type="html">{!! $entry->title() !!}</title>
        <link href="{{ $entry->url() }}"/>
        <id>{{ $entry->url() }}</id>
        @if ($entry->date)
        <published>{{ $entry->date->toRfc3339String() }}</published>
        @endif
        <updated>{{ $entry->getUpdatedAt()->toRfc3339String() }}</updated>
        @if ($entry->hasSummary() && $entry->hasImage())
            <summary type="html">
            @if ($entry->hasImage())
            <s:glide src="{{ $entry->image() }}" width="{{ $config->getImageWidth() }}" height="{{ $config->getImageHeight() }}">
            {{ $entry->encode('<p><img src="'.$config->makeUrlAbsolute($url).'" width="'.$width.'" height="'.$height.'"></p>') }}
            </s:glide>
            @endif
            {{ $entry->encode('<p>'.$entry->summary().'</p>') }}
            </summary>
        @elseif ($entry->hasSummary())
            <summary type="text">{{ $entry->summary() }}</summary>
        @else
            <summary></summary>
        @endif

        @if ($entry->hasContent())
            <content type="html">
            {{ $entry->encode($entry->content()) }}
            </content>
        @else
            <content src="{{ $entry->url() }}" type="text/html"></content>
        @endif

        <author>
        @if ($entry->hasAuthor())
            <name>{{ $entry->author()->name() }}</name>
            @if ($email = $entry->author()->email())
            <email>{{ $email }}</email>
            @endif
        @else
            <name>{{ $config->author_fallback_name }}</name>
            @if ($email = $config->author_fallback_email)
            <email>{{ $email }}</email>
            @endif
        @endif
        </author>

    </entry>
    @endforeach


</feed>