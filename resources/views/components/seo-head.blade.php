@props(['seo', 'jsonLd' => null])

@php
    // Prioritas: @section('title') → $seo['title'] → app.name
    $pageTitle = trim($__env->yieldContent('title')) ?: ($seo['title'] ?? config('app.name', 'MERCATORIA'));
    $pageDesc = $seo['description'] ?? '';
    $pageImage = $seo['image'] ?? '';
    $pageUrl = $seo['url'] ?? ($seo['canonical'] ?? '');
    $pageRobots = $seo['robots'] ?? 'index, follow';
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDesc }}">
<meta name="robots" content="{{ $pageRobots }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:url" content="{{ $pageUrl }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDesc }}">
<meta name="twitter:image" content="{{ $pageImage }}">

@if ($pageUrl)
    <link rel="canonical" href="{{ $pageUrl }}">
@endif

@if ($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd) !!}</script>
@endif