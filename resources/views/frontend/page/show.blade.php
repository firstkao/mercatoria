@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Halaman</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $page->title }}</h1>
        <div class="mt-5 h-px bg-rule"></div>
        <div class="mt-5 text-sm leading-7 text-ink/80">{!! nl2br(e($page->content)) !!}</div>
    </article>
@endsection
