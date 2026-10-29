<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-head :seo="$seo" :json-ld="$jsonLd" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="top" class="flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    {{-- 1. Top Promo Bar (PRD 40.1 / Design Spec 4.1) --}}
    <div id="promo-bar" class="relative bg-[#F5F7FA] px-10 py-1.5 text-center text-[11.5px] text-[#444]">
        <p>
            {{ setting('promo_bar_text', 'Gunakan kode promo NEWWEB dan nikmati potongan harga untuk belanja pertamamu.') }}
        </p>
        <button type="button" id="promo-bar-close" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#444] hover:text-ink" aria-label="Tutup">
            <x-icons.chevron-up class="rotate-90" width="12" height="12" />
        </button>
    </div>

    {{-- 2. Header (Design Spec 4.4.1) --}}
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto max-w-[1200px] px-4">
            <div class="hidden min-h-[72px] lg:grid lg:grid-cols-3 lg:items-center">
                <nav class="flex items-center gap-5" aria-label="Menu utama">
                    <a href="{{ route('home') }}" class="text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
                    <a href="#game-grid" class="text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Game</a>
                    <a href="#merch-baru" class="text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">Pre-order Baru</a>
                </nav>

                <a href="{{ route('home') }}" class="flex items-center justify-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-16" width="1608" height="494">
                </a>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('search') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Cari">
                        <x-icons.magnifying-glass width="18" height="18" />
                    </a>
                    <div class="flex items-center gap-2.5 text-[#333]">
                        <a href="{{ setting('url_instagram', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Instagram"><x-icons.instagram width="16" height="16" /></a>
                        <a href="{{ setting('url_facebook', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Facebook"><x-icons.facebook width="16" height="16" /></a>
                        <a href="{{ setting('url_x', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="X"><x-icons.twitter-x width="16" height="16" /></a>
                        <a href="{{ setting('url_threads', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="Threads"><x-icons.threads width="16" height="16" /></a>
                        <a href="{{ setting('url_whatsapp', '#') }}" class="transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp"><x-icons.whatsapp width="16" height="16" /></a>
                    </div>
                    @auth
                        <a href="{{ route('cart.index') }}" class="text-[#333] transition-colors hover:text-[#4FC3F7]" aria-label="Keranjang"><x-icons.shopping-cart width="18" height="18" /></a>
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]"><x-icons.user width="18" height="18" /></a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-[#333] transition-colors hover:text-[#4FC3F7]"><x-icons.user width="18" height="18" /></a>
                    @endauth
                </div>
            </div>

            <div class="flex min-h-[64px] items-center justify-between gap-3 lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center no-underline">
                    <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto" width="1608" height="494">
                </a>
                <a href="{{ route('login') }}" class="flex h-10 w-10 items-center justify-center text-[#333]" aria-label="Masuk">
                    <x-icons.user width="20" height="20" />
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1">
        {{-- 3. Hero (PRD 40.2 / Design Spec 4.4.2) --}}
        <section id="hero" class="mx-auto max-w-[1200px] px-4 py-6">
            @isset($heroSlides)
                <div class="relative overflow-hidden rounded-lg border border-rule">
                    <div class="relative aspect-[2/1] bg-ink">
                        @foreach ($heroSlides as $i => $slide)
                            <a href="{{ data_get($slide, 'url') ?? '#' }}" class="absolute inset-0 {{ $i === 0 ? '' : 'hidden' }}" data-hero-slide="{{ $i }}">
                                @if (data_get($slide, 'image'))
                                    <img src="{{ data_get($slide, 'image') }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-lg border border-rule bg-[#F5F7FA] px-8 py-16 text-center">
                    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Mercatoria</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold text-ink">Merch &amp; Collectibles untuk IP Favoritmu</h1>
                    <p class="mt-2 text-sm text-ink/65">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
                </div>
            @endisset
            <div class="mt-3 h-px bg-rule"></div>
        </section>

        {{-- 4. Jelajahi Game Favoritmu (PRD 40.4 / Design Spec 4.4.3) --}}
        <section id="game-grid" class="mx-auto max-w-[1200px] px-4 pb-12">
            <h2 class="text-center font-display text-[26px] font-semibold text-ink">Jelajahi Game Favoritmu</h2>
            <p class="mt-1 text-center text-[13px] text-ink/60">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori</p>

            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                @forelse ($games as $game)
                    <a href="{{ route('catalog.game', $game->slug) }}" class="group relative block aspect-square overflow-hidden rounded-lg bg-paper">
                        @if ($game->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-[#F5F7FA]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">{{ $game->name }}</span>
                            </div>
                        @endif
                        <div class="absolute bottom-2 left-1/2 w-[82%] -translate-x-1/2 rounded bg-white/92 px-2 py-1.5 text-center">
                            <p class="text-[10.5px] font-bold uppercase leading-tight text-ink">{{ $game->name }}</p>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-center text-sm text-ink/45">Game akan segera hadir.</p>
                @endforelse
            </div>
        </section>

        {{-- 5. Penawaran Terbaik (PRD 40.5) — data BE menyusul --}}
        @isset($bestOffers)
            <section id="penawaran-terbaik" class="mx-auto max-w-[1200px] px-4 pb-12">
                <h2 class="text-center font-display text-[22px] font-semibold text-ink">Penawaran Terbaik</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    @foreach ($bestOffers as $product)
                        <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="block rounded-lg border border-rule bg-white p-3">
                            @if ($product->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="aspect-square w-full rounded object-cover">
                            @endif
                            <p class="mt-2 text-[11.5px] font-bold leading-snug text-ink">
                                <span class="text-ink/60">[{{ $product->game->name }}]</span> {{ $product->name }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endisset

        {{-- 6. MERCH BARU (PRD 40.6 / Design Spec 4.4.4) --}}
        <section id="merch-baru" class="mx-auto max-w-[1200px] px-4 pb-14">
            <h2 class="text-center text-[18px] font-bold uppercase tracking-wide text-ink">MERCH BARU</h2>

            <div class="mt-5 grid grid-cols-2 gap-3.5 md:grid-cols-5 lg:grid-cols-7">
                @forelse ($products as $product)
                    <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="group block">
                        <div class="relative aspect-square overflow-hidden rounded bg-paper">
                            @if ($product->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                            @endif
                            @if ($product->tags->contains('slug', 'presale'))
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#29B6F6] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">PRESALE</span>
                            @elseif ($product->tags->contains('slug', 'limited'))
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#E53935] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">LIMITED</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-[11.5px] font-bold leading-[1.35] text-ink">
                            <span class="text-ink/55">[{{ $product->game->name }}]</span> {{ $product->name }}
                        </p>
                    </a>
                @empty
                    <p class="col-span-full text-center text-sm text-ink/45">Belum ada merch.</p>
                @endforelse
            </div>
        </section>
    </main>

    {{-- 7. Footer (Design Spec 4.4.5) --}}
    <footer id="footer" class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col items-center">
                    <a href="{{ route('home') }}" class="inline-flex flex-col items-center no-underline">
                        <img src="{{ asset('logo.png') }}" alt="Mercatoria" class="h-12 w-auto lg:h-14" width="1608" height="494">
                    </a>
                    <div class="mt-6 flex items-center justify-center gap-4">
                        <a href="{{ setting('url_instagram', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Instagram"><x-icons.instagram width="18" height="18" /></a>
                        <a href="{{ setting('url_facebook', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Facebook"><x-icons.facebook width="18" height="18" /></a>
                        <a href="{{ setting('url_x', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="X"><x-icons.twitter-x width="18" height="18" /></a>
                        <a href="{{ setting('url_threads', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Threads"><x-icons.threads width="18" height="18" /></a>
                        <a href="{{ setting('url_whatsapp', '#') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp"><x-icons.whatsapp width="18" height="18" /></a>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ setting('url_toco', '#') }}" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">TOCO <x-icons.arrow-right width="12" height="12" /></a>
                        <a href="{{ setting('url_shopee', '#') }}" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">SHOPEE <x-icons.arrow-right width="12" height="12" /></a>
                    </div>
                </div>

                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Game
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                    </summary>
                    <ul class="mt-3.5 text-[12px] leading-[1.8]">
                        @foreach ($games as $game)
                            <li><a href="{{ route('catalog.game', $game->slug) }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $game->name }}</a></li>
                        @endforeach
                    </ul>
                </details>

                <div class="space-y-8">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Jelajahi
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                        </summary>
                        <ul class="mt-3.5 text-[12px] leading-[1.8]">
                            <li><a href="{{ route('page.tentang-kami') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tentang Kami</a></li>
                            <li><a href="{{ route('page.faq') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tanya Jawab Umum</a></li>
                            <li><a href="{{ route('page.cara-belanja') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Cara Belanja</a></li>
                        </ul>
                    </details>
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Legal
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                        </summary>
                        <ul class="mt-3.5 text-[12px] leading-[1.8]">
                            <li><a href="{{ route('page.tnc') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Syarat &amp; Ketentuan</a></li>
                            <li><a href="{{ route('page.kebijakan-privasi') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Kebijakan Privasi</a></li>
                        </ul>
                    </details>
                </div>

                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Kontak
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                    </summary>
                    <p class="mt-3.5 text-[12px] leading-[1.8] text-[#B0B3C5]">info@mercatoria.id</p>
                </details>
            </div>
        </div>

        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; {{ date('Y') }} MERCATORIA</p>
                <p>Powered by MERCATORIA</p>
            </div>
        </div>
    </footer>

    <script>
        (function () {
            var bar = document.getElementById('promo-bar');
            var closeBtn = document.getElementById('promo-bar-close');
            if (bar && localStorage.getItem('promo-bar-dismissed') === '1') {
                bar.style.display = 'none';
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    if (bar) bar.style.display = 'none';
                    localStorage.setItem('promo-bar-dismissed', '1');
                });
            }
        })();
    </script>
</body>
</html>
