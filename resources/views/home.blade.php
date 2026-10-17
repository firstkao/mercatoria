@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
@if (session('blocked_spammer_ip'))
    <div role="alert" class="mx-auto mt-4 max-w-7xl rounded-lg bg-danger-50 px-4 py-3 text-sm text-danger-700">
        Akses dari alamat jaringan ini dibatasi. Halaman beranda tetap dapat diakses.
    </div>
@endif
<section class="bg-surface-2">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:grid-cols-2 md:items-center md:py-24">
        <div>
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-navy-500">
                Merchandise IP Cina, lebih mudah
            </p>
            <h1 class="max-w-2xl text-4xl font-bold leading-tight text-text md:text-5xl">
                Koleksi merch favoritmu. Urusan belinya, biar kami bantu.
            </h1>
            <p class="mt-5 max-w-xl text-lg text-text-muted">
                Mercatoria membantu penggemar di Indonesia menemukan dan membeli merchandise game, anime, donghua, dan novel dari Tiongkok.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button variant="ghost" href="{{ route('catalog.index') }}">Jelajahi katalog</x-button>
                <x-button href="{{ route('register') }}">Mulai belanja</x-button>
                <x-button variant="ghost" href="{{ route('login') }}">Masuk ke akun</x-button>
            </div>
        </div>

        <div class="rounded-2xl border border-border bg-white p-6 shadow-sm md:p-8">
            <h2 class="text-xl font-semibold text-navy-500">Belanja lintas negara, tanpa ribet</h2>
            <ul class="mt-5 space-y-4 text-sm text-text-muted">
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Katalog terkurasi untuk menemukan merchandise berdasarkan IP favorit.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Pembayaran lokal dan informasi harga dalam Rupiah.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-bold text-success-500" aria-hidden="true">✓</span>
                    <span>Proses pembelian dan pengiriman dibantu oleh Mercatoria.</span>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 text-center">
    <h2 class="text-2xl font-bold text-text">Mulai dari fandom favoritmu</h2>
    <p class="mx-auto mt-3 max-w-2xl text-text-muted">
        Buat akun untuk mengikuti perjalanan pesanan dan mendapat kabar seputar koleksi terbaru.
    </p>
    <x-button href="{{ route('register') }}" class="mt-6">Buat akun Mercatoria</x-button>
</section>
@endsection

@push('scripts')
    <script>
        if (typeof window.mercatoriaTrack === 'function') {
            window.mercatoriaTrack('view_home');
        }
    </script>
@endpush

@push('structured-data')
    <script type="application/ld+json">
        {!! json_encode([
            chr(64).'context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Mercatoria',
            'url' => route('home'),
            'logo' => setting('seo_default_og_image')
                ? url(setting('seo_default_og_image'))
                : null,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endpush
