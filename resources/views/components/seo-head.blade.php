@props(['seo', 'jsonLd' => null])

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta property="og:url" content="{{ $seo['url'] }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">

<link rel="canonical" href="{{ $seo['url'] }}">

@if ($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd) !!}</script>
@endif
