@extends('layouts.app')

@section('title', $seo['title'])

@section('content')
    <article class="mx-auto max-w-6xl">
        <h1 class="sr-only">{{ $page->title }}</h1>
        <div class="preorder-content">{!! $page->content !!}</div>
    </article>
@endsection
