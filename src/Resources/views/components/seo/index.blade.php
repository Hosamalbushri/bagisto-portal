@props([
    'title'       => null,
    'description' => null,
    'keywords'    => null,
    'image'       => null,
    'type'        => 'website',
    'url'         => null,
    'publishedAt' => null,
    'modifiedAt'  => null,
    'author'      => null,
])

@php
    $channel = core()->getCurrentChannel();
    $currentLocale = app()->getLocale();

    $resolvedTitle = trim(($title ? $title . ' | ' : '') . ($channel?->name ?? config('app.name', 'Bagisto Portal')));
    $resolvedDesc = $description ?: ($channel?->description ?? config('app.description', 'Modern portal and content engine.'));
    $resolvedUrl = $url ?: url()->current();
    $resolvedImage = $image ?: ($channel?->logo_url ?? portal_asset('images/hero-image.webp', asset('images/logo.svg')));
    $siteName = $channel?->name ?? config('app.name', 'Bagisto');

    $schemaData = array_filter([
        '@context'      => 'https://schema.org',
        '@type'         => $type === 'article' ? 'Article' : 'WebPage',
        'name'          => $resolvedTitle,
        'description'   => $resolvedDesc,
        'url'           => $resolvedUrl,
        'image'         => $resolvedImage,
        'datePublished' => $publishedAt ? (is_string($publishedAt) ? $publishedAt : $publishedAt->toIso8601String()) : null,
        'dateModified'  => $modifiedAt ? (is_string($modifiedAt) ? $modifiedAt : $modifiedAt->toIso8601String()) : null,
        'author'        => $author ? ['@type' => 'Person', 'name' => $author] : null,
        'publisher'     => ['@type' => 'Organization', 'name' => $siteName, 'url' => url('/')],
    ]);
@endphp

<!-- Primary Meta Tags -->
@if ($description)
    <meta name="description" content="{{ $resolvedDesc }}">
@endif

@if ($keywords)
    <meta name="keywords" content="{{ $keywords }}">
@endif

<link rel="canonical" href="{{ $resolvedUrl }}">

<!-- Multilingual SEO Alternate Hreflang Tags -->
@if (isset($channel->locales) && count($channel->locales) > 1)
    @foreach ($channel->locales as $channelLocale)
        <link rel="alternate" hreflang="{{ $channelLocale->code }}" href="{{ $resolvedUrl }}?locale={{ $channelLocale->code }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $resolvedUrl }}">
@endif

<!-- Open Graph / Facebook / LinkedIn -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $resolvedUrl }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDesc }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $currentLocale) }}">
@if ($resolvedImage)
    <meta property="og:image" content="{{ $resolvedImage }}">
@endif

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $resolvedUrl }}">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDesc }}">
@if ($resolvedImage)
    <meta name="twitter:image" content="{{ $resolvedImage }}">
@endif

<!-- Schema.org WebPage / Article Structured Data -->
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
