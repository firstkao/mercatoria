@extends('layouts.app')

@section('title', $title)
@section('meta_description', $description ?: \Illuminate\Support\Str::limit(trim(strip_tags($content)), 160))
@section('meta_type', 'article')
@section('meta_image', $page->featured_image_path
    ? (str_starts_with($page->featured_image_path, 'http') ? $page->featured_image_path : url($page->featured_image_path))
    : '')
@section('canonical_url', route('content.show', $page->slug))

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-10">
        <h1 class="text-3xl font-bold text-navy-500">{{ $page->title }}</h1>
        <div class="prose prose-slate mt-8 max-w-none">{!! $content !!}</div>
    </article>
@endsection

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([chr(64).'context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $title, 'url' => url()->current(), 'description' => $description], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush
