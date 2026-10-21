<!DOCTYPE html>
<html lang="id" data-image-protection="{{ filter_var(setting('image_protection_enabled', config('mercatoria.image_protection.enabled', true)), FILTER_VALIDATE_BOOLEAN) ? '1' : '0' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoTitle = trim($__env->yieldContent('title', setting('seo_default_title', 'Mercatoria — Marketplace Merchandise IP Cina')));
        $seoDescription = trim($__env->yieldContent('meta_description', setting('seo_default_description', 'Temukan merchandise favoritmu di Mercatoria.')));
        $seoImage = trim($__env->yieldContent('meta_image', setting('seo_default_og_image', '')));
        $seoImage = $seoImage !== '' && ! str_starts_with($seoImage, 'http') ? url($seoImage) : $seoImage;
        $canonicalUrl = trim($__env->yieldContent('canonical_url', url()->current()));
        $seoType = trim($__env->yieldContent('meta_type', 'website'));
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="Mercatoria">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:type" content="{{ $seoType }}">
    @if ($seoImage !== '')
        <meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
    <meta name="twitter:card" content="{{ $seoImage !== '' ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('seo')
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-white text-text antialiased">

    <x-announcement-bar />

    @auth
        <x-navbar-user />
    @else
        <x-navbar-guest />
    @endauth

    <x-verify-banner-compact />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-footer />

    <x-toast />

    <x-ga4-tracking />
    @stack('structured-data')

    @stack('scripts')
</body>
</html>