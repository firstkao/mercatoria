@props(['paginator'])

@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, 2, 3, 4, $current - 1, $current, $current + 1, $last - 2, $last - 1, $last])
            ->filter(fn ($page) => $page >= 1 && $page <= $last)
            ->unique()
            ->sort()
            ->values();
        $box = 'flex h-9 min-w-9 items-center justify-center border border-border bg-white px-2 text-sm text-text hover:border-primary-300 hover:text-primary-400';
    @endphp
    <nav aria-label="Pagination" class="mt-8 flex flex-wrap items-center justify-center gap-1.5">
        @if ($paginator->onFirstPage() === false)
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" class="{{ $box }}">←</a>
        @endif
        @foreach ($pages as $position => $page)
            @if ($position > 0 && $page - $pages[$position - 1] > 1)
                <span class="flex h-9 min-w-9 items-center justify-center text-sm text-text-muted" aria-hidden="true">…</span>
            @endif
            @if ($page === $current)
                <span aria-current="page" class="flex h-9 min-w-9 items-center justify-center border border-primary-300 bg-primary-300 px-2 text-sm font-bold text-white">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $box }}">{{ $page }}</a>
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" class="{{ $box }}">→</a>
        @endif
    </nav>
@endif
