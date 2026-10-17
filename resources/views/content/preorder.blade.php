@extends('layouts.app')

@section('title', $title)
@section('meta_description', $description ?: \Illuminate\Support\Str::limit(trim(strip_tags($content)), 160))
@section('canonical_url', route('content.show', $page->slug))

@section('content')
    <article class="mx-auto max-w-5xl px-4 py-10">
        <h1 class="text-3xl font-bold text-navy-500">{{ $page->title }}</h1>
        <div class="prose prose-slate mt-8 max-w-none">{!! $content !!}</div>
    </article>
@endsection
