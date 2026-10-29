@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-semibold">{{ $page->title }}</h1>
        <div class="prose mt-4 text-sm leading-7 text-gray-700">{!! nl2br(e($page->content)) !!}</div>
    </article>
@endsection
