{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ setting('site_name', config('app.name')) }} Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>{{ setting('seo_meta_description', setting('site_tagline')) }}</description>
        <language>en</language>
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml" />
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ $post->url() }}</link>
            <guid isPermaLink="true">{{ $post->url() }}</guid>
            <description>{{ $post->excerptText(300) }}</description>
            <pubDate>{{ ($post->published_at ?? $post->created_at)->toRssString() }}</pubDate>
        </item>
@endforeach
    </channel>
</rss>
