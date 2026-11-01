@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Halaman</p>
        <h1 class="mt-2 font-display text-[1.75rem] leading-tight font-semibold text-ink sm:text-3xl">{{ $page->title }}</h1>
        <div class="mt-5 h-px bg-rule"></div>
        <div class="cms-prose mt-6 text-[15px] text-ink/85">{!! $page->content !!}</div>
    </article>

    <style>
        .cms-prose { line-height: 1.75; }
        .cms-prose p { margin: 0 0 1em; }
        .cms-prose p:last-child { margin-bottom: 0; }
        .cms-prose h2 { margin: 1.75em 0 0.6em; font-family: 'Fraunces', Georgia, serif; font-size: 1.35rem; font-weight: 600; color: #141B22; line-height: 1.35; }
        .cms-prose h3 { margin: 1.4em 0 0.5em; font-family: 'Fraunces', Georgia, serif; font-size: 1.1rem; font-weight: 600; color: #141B22; line-height: 1.4; }
        .cms-prose ul, .cms-prose ol { margin: 0 0 1em; padding-left: 1.35em; }
        .cms-prose li { margin: 0.3em 0; }
        .cms-prose ul { list-style: disc; }
        .cms-prose ol { list-style: decimal; }
        .cms-prose a { color: #0C6FA6; text-decoration: underline; text-underline-offset: 3px; }
        .cms-prose strong { color: #141B22; font-weight: 600; }
    </style>
@endsection
