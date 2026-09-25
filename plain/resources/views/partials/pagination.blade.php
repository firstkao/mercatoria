@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="pagination__link is-disabled">Sebelumnya</span>
        @else
            <a class="pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        <span class="pagination__info">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="pagination__link is-disabled">Berikutnya</span>
        @endif
    </nav>
@endif