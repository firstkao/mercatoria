<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-head :seo="$seo" :json-ld="$jsonLd" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased">
    {{-- 1. Top Promo Bar (PRD 40) --}}
    <div id="promo-bar" class="bg-black px-4 py-2 text-center text-xs text-white">
        Gunakan kode promo NEWWEB dan nikmati potongan harga untuk belanja pertamamu.
    </div>

    {{-- 2. Header --}}
    <header class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <nav class="flex gap-4 text-sm font-medium">
            <a href="/">Beranda</a>
            <a href="#game-grid">Game</a>
            <a href="#merch-baru">Pre-order Baru</a>
        </nav>
        <a href="/" class="flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-black text-sm font-semibold text-white">M</span>
            <span class="font-semibold tracking-wide">MERCATORIA</span>
        </a>
        <div class="flex items-center gap-3 text-sm">
            @auth
                <a href="{{ route('cart.index') }}">Keranjang</a>
                <a href="{{ route('dashboard') }}" class="font-medium">{{ auth()->user()->name }}</a>
            @else
                <a href="{{ route('login') }}" class="font-medium">Masuk</a>
            @endauth
        </div>
    </header>

    {{-- 3. Hero --}}
    <section id="hero" class="mx-auto max-w-7xl px-4 py-10">
        <div class="rounded-2xl bg-gray-900 px-8 py-16 text-center text-white">
            <h1 class="text-3xl font-semibold">Merch &amp; Collectibles untuk IP Favoritmu</h1>
            <p class="mt-2 text-sm text-gray-300">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
        </div>
    </section>

    {{-- 4. Jelajahi Game (PRD 40) --}}
    <section id="game-grid" class="mx-auto max-w-7xl px-4 pb-10">
        <h2 class="text-center text-xl font-semibold">Jelajahi Game Favoritmu</h2>
        <p class="mt-1 text-center text-sm text-gray-500">Temukan merchandise eksklusif dari game favoritmu.</p>
        <div class="mt-6 grid grid-cols-3 gap-4 md:grid-cols-6">
            @forelse ($games as $game)
                <a href="{{ route('catalog.game', $game->slug) }}" class="rounded-xl bg-gray-50 p-4 text-center text-sm font-medium ring-1 ring-gray-100 hover:shadow">
                    {{ $game->name }}
                </a>
            @empty
                <p class="col-span-full text-center text-sm text-gray-400">Game akan segera hadir.</p>
            @endforelse
        </div>
    </section>

    {{-- 5. Merch Baru (PRD 40) --}}
    <section id="merch-baru" class="mx-auto max-w-7xl px-4 pb-10">
        <h2 class="text-center text-xl font-semibold">MERCH BARU</h2>
        <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-7">
            @forelse ($products as $product)
                <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}"
                   class="rounded-xl bg-white p-3 text-sm ring-1 ring-gray-100 hover:shadow">
                    <p class="text-[10px] uppercase text-gray-400">[{{ $product->game->name }}]</p>
                    <p class="mt-1 line-clamp-2 font-medium">{{ $product->name }}</p>
                </a>
            @empty
                <p class="col-span-full text-center text-sm text-gray-400">Belum ada merch.</p>
            @endforelse
        </div>
    </section>

    {{-- 6. Footer (PRD 40) --}}
    <footer id="footer" class="bg-[#1E1E2D] px-4 py-10 text-sm text-gray-300">
        <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-4">
            <div>
                <p class="font-semibold text-white">MERCATORIA</p>
                <p class="mt-2 text-xs">Merch &amp; Collectibles Store</p>
            </div>
            <div>
                <p class="font-semibold text-white">Jelajahi</p>
                <ul class="mt-2 space-y-1 text-xs">
                    <li><a href="{{ route('page.tentang-kami') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('page.faq') }}">Tanya Jawab Umum</a></li>
                    <li><a href="{{ route('page.cara-belanja') }}">Cara Belanja</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-white">Legal</p>
                <ul class="mt-2 space-y-1 text-xs">
                    <li><a href="{{ route('page.tnc') }}">Syarat &amp; Ketentuan</a></li>
                    <li><a href="{{ route('page.kebijakan-privasi') }}">Kebijakan Privasi</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-white">Kontak</p>
                <p class="mt-2 text-xs">info@mercatoria.id</p>
            </div>
        </div>
        <p class="mx-auto mt-8 max-w-7xl text-center text-xs text-gray-500">© {{ date('Y') }} MERCATORIA</p>
    </footer>
</body>
</html>
