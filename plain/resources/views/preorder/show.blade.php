@extends('layouts.app', ['title' => $page?->title ?? 'Pre-order Baru'])

@section('content')
    {{-- Empty-state: menu header selalu tampil, jadi halaman ini bisa diakses
         walau admin belum membuat halaman PO aktif. --}}
    @unless ($page)
        <div class="preorder-empty">
            <div class="preorder-empty__icon">📦</div>
            <h1>Belum ada Pre-order Aktif</h1>
            <p>Santai, gelombang pre-order berikutnya sedang disiapkan.<br>
               Sambil ngopi dulu, cek <a href="{{ route('catalog.index') }}">katalog produk</a> yang sudah ready ya!</p>
        </div>
    @endunless
    @if ($page)
    <article class="preorder-page">
        {{-- Banner carousel (kalau ada lebih dari 1) --}}
        @if ($banners->isNotEmpty())
            <section class="preorder-banners" data-preorder-banners>
                @if ($banners->count() === 1)
                    {{-- Single banner --}}
                    @php($banner = $banners->first())
                    <div class="preorder-banner">
                        @if ($banner->link_url)
                            <a href="{{ $banner->link_url }}" @if (! str_starts_with($banner->link_url, '/')) target="_blank" rel="noopener" @endif>
                                <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->alt_text ?? $page->title }}">
                            </a>
                        @else
                            <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->alt_text ?? $page->title }}">
                        @endif
                    </div>
                @else
                    {{-- Multiple banners: simple scroll carousel --}}
                    <div class="preorder-carousel">
                        @foreach ($banners as $banner)
                            <div class="preorder-carousel__slide">
                                @if ($banner->link_url)
                                    <a href="{{ $banner->link_url }}" @if (! str_starts_with($banner->link_url, '/')) target="_blank" rel="noopener" @endif>
                                        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->alt_text ?? $page->title }}">
                                    </a>
                                @else
                                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->alt_text ?? $page->title }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    {{-- Dots --}}
                    <div class="preorder-carousel__dots" data-carousel-dots>
                        @foreach ($banners as $i => $banner)
                            <button type="button" class="preorder-carousel__dot {{ $i === 0 ? 'is-active' : '' }}" data-carousel-go="{{ $i }}"></button>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        {{-- Konten tulisan --}}
        <section class="card prose preorder-content">
            <h1>{{ $page->title }}</h1>
            {!! $page->html() !!}
        </section>
    </article>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    var carousel = document.querySelector('.preorder-carousel');
    if (!carousel) return;

    var slides = carousel.querySelectorAll('.preorder-carousel__slide');
    var dots = document.querySelectorAll('[data-carousel-go]');
    var total = slides.length;
    var current = 0;
    var interval;

    function goTo(index) {
        current = index;
        carousel.scrollTo({ left: carousel.clientWidth * index, behavior: 'smooth' });
        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(dot.dataset.carouselGo, 10));
            restartAuto();
        });
    });

    function next() {
        goTo((current + 1) % total);
    }

    function restartAuto() {
        clearInterval(interval);
        if (total > 1) interval = setInterval(next, 5000);
    }

    // Update dot saat user scroll manual
    carousel.addEventListener('scroll', function () {
        var idx = Math.round(carousel.scrollLeft / carousel.clientWidth);
        if (idx !== current) {
            current = idx;
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === idx);
            });
        }
    }, { passive: true });

    if (total > 1) interval = setInterval(next, 5000);
})();
</script>
@endpush