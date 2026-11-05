@extends('layouts.app')

@section('title', $page->title)

@section('content')
    @include('frontend.partials._auth-style')

    <style>
        body { background: #f7f7f7; }

        .cm, .cm *, .cm *::before, .cm *::after { box-sizing: border-box; }
        .cm { font-family: 'Roboto', system-ui, sans-serif; color: #333; -webkit-font-smoothing: antialiased; }
        .cm a { text-decoration: none; }

        .cm-wrap { max-width: 720px; margin: 0 auto; }
        .cm-eyebrow { margin: 0; font-size: 11px; line-height: 16px; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: #888; }
        .cm-title { margin: 8px 0 0; font-size: 28px; line-height: 34px; font-weight: 700; color: #000; }
        .cm-hr { height: 0; margin: 24px 0 0; border: 0; border-top: 1px solid #e1e1e1; }
        .cm-prose { margin-top: 24px; font-size: 15px; line-height: 26px; color: #333; }
        .cm-prose p { margin: 0 0 1em; }
        .cm-prose p:last-child { margin-bottom: 0; }
        .cm-prose h2 { margin: 1.75em 0 0.6em; font-size: 20px; line-height: 28px; font-weight: 700; color: #000; }
        .cm-prose h3 { margin: 1.4em 0 0.5em; font-size: 17px; line-height: 24px; font-weight: 700; color: #000; }
        .cm-prose ul, .cm-prose ol { margin: 0 0 1em; padding-left: 1.35em; }
        .cm-prose li { margin: 0.3em 0; }
        .cm-prose ul { list-style: disc; }
        .cm-prose ol { list-style: decimal; }
        .cm-prose a { color: #02a1ea; }
        .cm-prose a:hover { text-decoration: underline; }
        .cm-prose strong { color: #000; font-weight: 700; }
    </style>

    <article class="cm cm-wrap">
        <p class="cm-eyebrow">Halaman</p>
        <h1 class="cm-title">{{ $page->title }}</h1>
        <div class="cm-hr"></div>
        <div class="cm-prose">{!! $page->content !!}</div>
    </article>
@endsection
