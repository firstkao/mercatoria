{{-- Paginasi katalog. Simpan di: resources/views/vendor/pagination/catalog-pager.blade.php
     Gaya (.cgp-*) ada di view katalog. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginasi">
        <ul class="cgp-list">
            @unless ($paginator->onFirstPage())
                <li><a class="cgp-b" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">&larr;</a></li>
            @endunless

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="cgp-b">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="cgp-b is-on" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="cgp-b" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="cgp-b" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">&rarr;</a></li>
            @endif
        </ul>
    </nav>
@endif
