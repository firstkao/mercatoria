@props(['mobile' => false])

@php
    $games = \App\Models\Game::active()->orderBy('name')->get(['id', 'name', 'slug']);
    $developers = \App\Models\Developer::active()->orderBy('name')->get(['id', 'name', 'slug']);
    $preorderPage = \App\Models\PreorderPage::published()->latest('id')->first(['id', 'slug']);
    $preorderHref = $preorderPage ? route('content.show', $preorderPage->slug) : route('catalog.index', ['sort' => 'latest']);

    $size = $mobile ? 'block px-3 py-2 text-sm' : 'px-3 py-2 text-sm';
    $linkClass = fn (bool $active) => $active ? 'font-semibold text-navy-500' : 'text-text-muted hover:text-text';
@endphp

<a href="{{ route('home') }}" class="{{ $size }} {{ $linkClass(request()->routeIs('home')) }}">Beranda</a>

<div x-data="{ open: false }" @if (! $mobile) @mouseenter="open = true" @mouseleave="open = false" @endif @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open"
        class="{{ $size }} {{ $mobile ? 'w-full text-left' : '' }} {{ $linkClass(request()->routeIs('games.*')) }}">Game ▾</button>
    <div x-show="open" x-cloak
        class="{{ $mobile ? 'max-h-60 overflow-y-auto pl-4' : 'absolute left-0 top-full z-50 max-h-80 w-56 overflow-y-auto rounded-lg border border-border bg-white py-1 shadow-lg' }}">
        @forelse ($games as $game)
            <a href="{{ route('games.show', $game) }}" class="block px-4 py-2 text-sm text-text hover:bg-surface-2">{{ $game->name }}</a>
        @empty
            <p class="px-4 py-2 text-sm text-text-light">Belum ada game.</p>
        @endforelse
    </div>
</div>

<div x-data="{ open: false }" @if (! $mobile) @mouseenter="open = true" @mouseleave="open = false" @endif @click.outside="open = false" class="relative">
    <button type="button" @click="open = !open" :aria-expanded="open"
        class="{{ $size }} {{ $mobile ? 'w-full text-left' : '' }} {{ $linkClass(request()->routeIs('developers.*')) }}">Developer ▾</button>
    <div x-show="open" x-cloak
        class="{{ $mobile ? 'max-h-60 overflow-y-auto pl-4' : 'absolute left-0 top-full z-50 max-h-80 w-56 overflow-y-auto rounded-lg border border-border bg-white py-1 shadow-lg' }}">
        @forelse ($developers as $developer)
            <a href="{{ route('developers.show', $developer) }}" class="block px-4 py-2 text-sm text-text hover:bg-surface-2">{{ $developer->name }}</a>
        @empty
            <p class="px-4 py-2 text-sm text-text-light">Belum ada developer.</p>
        @endforelse
    </div>
</div>

<a href="{{ $preorderHref }}" class="{{ $size }} {{ $linkClass(false) }}">Pre-order Baru</a>
