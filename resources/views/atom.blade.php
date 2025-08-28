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

    <generator uri="https://github.com/mitydigital/feedamic" version="{{ \MityDigital\Feedamic\Facades\Feedamic::version() }}">{{ __('feedamic::feeds.generator') }}</generator>

    @foreach ($entries as $entry)
    <entry>
        <title type="{{ $entry->isHtml($entry->title()) ? 'html' : 'text' }}">{!! $entry->encode($entry->title()) !!}</title>
        @if ($entry->hasUrl())
        <link href="{{ $entry->url() }}"/>
        <id>{{ $entry->url() }}</id>
        @else
        <id>{{ $entry->id }}</id>
        @endif
        @if ($entry->date)
        <published>{{ $entry->date->toRfc3339String() }}</published>
        @endif
        <updated>{{ $entry->getUpdatedAt()->toRfc3339String() }}</updated>
        @if ($entry->hasSummary() && $entry->hasImage())
            <summary type="html">
            @if (is_string($entry->image()))
            {{ '<p><img src="'.$entry->image().'" width="'.$config->getImageWidth().'" height="'.$config->getImageHeight().'" alt="'.$entry->title().'"></p>' }}
            @else
            <s:glide src="{{ $entry->image() }}" width="{{ $config->getImageWidth() }}" height="{{ $config->getImageHeight() }}">
            {{ '<p><img src="'.$config->makeUrlAbsolute($url).'" width="'.$width.'" height="'.$height.'" alt="'.$entry->title().'"></p>' }}
            </s:glide>
            @endif
            {{ '<p>'.$entry->summary().'</p>' }}
            </summary>
        @elseif ($entry->hasSummary())
            <summary type="text">{{ $entry->summary() }}</summary>
        @else
            <summary></summary>
        @endif

        @if ($entry->hasContent())
            <content type="{{ $entry->isHtml($entry->content()) ? 'html' : 'text' }}">
            {{ $entry->content() }}
            </content>
        @elseif ($entry->hasUrl())
            <content src="{{ $entry->url() }}" type="text/html"></content>
        @else
            <content></content>
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