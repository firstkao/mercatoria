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
    {{-- 1. Top Promo Bar (DS 4.4.1 / PRD 40.1) --}}
    @if (setting('promo_bar_active', '1') !== '0')
        <div id="promo-bar" class="relative bg-[#FFC107] px-10 py-2.5 text-center">
            <p class="text-[13px] font-semibold text-[#1a1a1a]">
                <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-[#8B6914]">KODE:</span>
                <strong class="mx-1 font-mono text-[13px] tracking-[0.08em] text-[#1a1a1a]">
                    {{ setting('promo_bar_text', 'NEWWEB') }}
                </strong>
                @if (setting('promo_bar_link'))
                    <a href="{{ setting('promo_bar_link') }}" class="ml-2 font-medium text-[#1a1a1a] underline decoration-[#8B6914]/50 underline-offset-2 hover:decoration-[#1a1a1a]">
                        Lihat Promo
                    </a>
                @endif
            </p>
            <button type="button" id="promo-bar-close"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#1a1a1a]/60 transition-colors hover:text-[#1a1a1a]"
                    aria-label="Tutup">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/></svg>
            </button>
        </div>
    @endif

    {{-- 2. Header (Design Spec 4.4.1) --}}
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto max-w-[1200px] px-4">
            <div class="hidden min-h-[72px] lg:grid lg:grid-cols-3 lg:items-center">
                <nav class="flex items-center gap-5" aria-label="Menu utama">
                    <a href="{{ route('home') }}" class="text-[13px] font-medium text-[#4FC3F7]">Beranda</a>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Game
                            <span class="text-[10px] transition-transform group-open:rotate-180">▾</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @forelse ($games as $g)
                                <a href="{{ route('catalog.game', $g->slug) }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ $g->name }}</a>
                            @empty
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada game</span>
                            @endforelse
                        </div>
                    </details>

                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1 text-[13px] font-medium text-[#333] transition-colors hover:text-[#4FC3F7]">
                            Developer
                            <span class="text-[10px] transition-transform group-open:rotate-180">▾</span>
                        </summary>
                        <div class="absolute left-0 top-full z-20 mt-2 max-h-80 w-56 overflow-y-auto rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                            @isset($footerDevelopers)
                                @forelse ($footerDevelopers as $dev)
                                    <a href="{{ data_get($dev, 'url') ?? (data_get($dev, 'slug') ? route('catalog.developer', data_get($dev, 'slug')) : '#') }}" class="block px-3.5 py-1.5 text-[12px] text-[#333] transition-colors hover:bg-[#F5F7FA] hover:text-[#4FC3F7]">{{ data_get($dev, 'name') }}</a>
                                @empty
                                    <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                                @endforelse
                            @else
                                <span class="block px-3.5 py-1.5 text-[12px] text-[#999]">Belum ada developer</span>
                            @endisset
                        </div>
                    </details>

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
                <details class="relative">
                    <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center text-[#333]" aria-label="Menu">
                        <x-icons.list width="22" height="22" />
                    </summary>
                    <div class="absolute left-0 top-full z-20 mt-2 w-64 rounded-lg border border-[#EAEAEA] bg-white py-2 shadow-sm">
                        <a href="{{ route('home') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#4FC3F7]">Beranda</a>
                        <a href="#game-grid" class="block px-4 py-2.5 text-[13px] text-[#333]">Game</a>
                        <a href="#merch-baru" class="block px-4 py-2.5 text-[13px] text-[#333]">Pre-order Baru</a>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Game</p>
                            @foreach ($games as $g)
                                <a href="{{ route('catalog.game', $g->slug) }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ $g->name }}</a>
                            @endforeach
                        </div>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <p class="px-4 py-1.5 font-mono text-[10px] uppercase tracking-wider text-ink/45">Developer</p>
                            @isset($footerDevelopers)
                                @foreach ($footerDevelopers as $dev)
                                    <a href="{{ data_get($dev, 'url') ?? (data_get($dev, 'slug') ? route('catalog.developer', data_get($dev, 'slug')) : '#') }}" class="block px-4 py-2 text-[12px] text-[#333]">{{ data_get($dev, 'name') }}</a>
                                @endforeach
                            @endisset
                        </div>
                        <div class="mt-1 border-t border-[#EAEAEA] pt-1">
                            <a href="{{ route('login') }}" class="block px-4 py-2.5 text-[13px] font-medium text-[#333]">Masuk</a>
                        </div>
                    </div>
                </details>

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
        {{-- 3. Hero Carousel (PRD 40.2) --}}
        <section id="hero" class="mx-auto max-w-[1200px] px-4 py-6">
            @isset($heroSlides)
                @if (count($heroSlides) > 0)
                    <div class="relative overflow-hidden rounded-lg border border-rule" id="hero-carousel">
                        <div class="relative aspect-[2/1] bg-ink" id="hero-track">
                            @foreach ($heroSlides as $i => $slide)
                                <a href="{{ data_get($slide, 'url') ?? '#' }}" class="absolute inset-0 {{ $i === 0 ? '' : 'hidden' }}" data-hero-slide="{{ $i }}">
                                    @if (data_get($slide, 'image'))
                                        <img src="{{ data_get($slide, 'image') }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </a>
                            @endforeach
                        </div>

                        <button type="button" id="hero-prev" class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Sebelumnya">
                            <x-icons.chevron-up class="rotate-90" width="16" height="16" />
                        </button>
                        <button type="button" id="hero-next" class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/70 text-ink transition-colors hover:bg-white" aria-label="Berikutnya">
                            <x-icons.chevron-up class="-rotate-90" width="16" height="16" />
                        </button>

                        <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-2" id="hero-dots">
                            @foreach ($heroSlides as $i => $slide)
                                <button type="button" data-hero-dot="{{ $i }}" class="h-2 w-2 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/45' }}" aria-label="Slide {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-rule bg-[#F5F7FA] px-8 py-16 text-center">
                        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Mercatoria</p>
                        <h1 class="mt-2 font-display text-3xl font-semibold text-ink">Merch &amp; Collectibles untuk IP Favoritmu</h1>
                        <p class="mt-2 text-sm text-ink/65">Harga bersih — sudah termasuk ongkir CN-ID &amp; bea.</p>
                    </div>
                @endif
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

        {{-- 5. Penawaran Terbaik (PRD 40.5 / DS 4.6.3) --}}
        @if(($bestOffers ?? collect())->isNotEmpty())
            <section id="penawaran-terbaik" class="mx-auto max-w-[1200px] px-4 pb-12">
                <h2 class="text-center font-display text-[22px] font-semibold text-ink">Penawaran Terbaik</h2>
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    @foreach ($bestOffers as $product)
                        <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="group block">
                            <div class="relative aspect-square overflow-hidden rounded-lg bg-paper">
                                @if ($product->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                                @endif
                                <span class="absolute left-0 top-2 -rotate-45 bg-[#E53935] px-3 py-0.5 text-[9.5px] font-bold uppercase text-white">DISKON</span>
                            </div>
                            <p class="mt-2 text-[11.5px] font-bold leading-snug text-ink">
                                <span class="text-ink/60">[{{ $product->game->name }}]</span> {{ $product->name }}
                            </p>
                            @if ($product->hasActiveDiscount() && !empty($product->price_idr))
                                <p class="mt-0.5 text-xs text-ink/45 line-through">{{ format_rp($product->price_idr + (float) $product->discount_amount) }}</p>
                                <p class="text-[11.5px] font-bold text-[#29B6F6]">{{ format_rp($product->price_idr) }}</p>
                            @elseif(!empty($product->price_idr))
                                <p class="mt-0.5 text-[11.5px] font-bold text-[#29B6F6]">{{ format_rp($product->price_idr) }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

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
                        @if (!empty($product->price_idr))
                            <p class="mt-0.5 text-[11.5px] font-bold text-[#29B6F6]">{{ format_rp($product->price_idr) }}</p>
                        @endif
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
                        Produk Terpopuler
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                    </summary>
                    <ul class="mt-3.5 space-y-2 text-[12px]">
                        @isset($footerTopProducts)
                            @foreach ($footerTopProducts as $product)
                                <li class="flex items-center gap-2.5 border-b border-white/10 pb-2">
                                    @if (data_get($product, 'image'))
                                        <img src="{{ data_get($product, 'image') }}" alt="" class="h-12 w-12 shrink-0 rounded object-cover">
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ data_get($product, 'url') ?? '#' }}" class="block truncate text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">
                                            {{ data_get($product, 'name') }}
                                        </a>
                                        @if (data_get($product, 'price') !== null)
                                            <span class="text-white">{{ format_rp(data_get($product, 'price')) }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        @endisset
                    </ul>
                </details>
            </div>
        </div>

        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; {{ now()->year }} MERCATORIA</p>
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

            var carousel = document.getElementById('hero-carousel');
            if (!carousel) return;
            var slides = carousel.querySelectorAll('[data-hero-slide]');
            var dots = carousel.querySelectorAll('[data-hero-dot]');
            if (slides.length < 2) return;
            var index = 0;
            var timer = null;

            function show(i) {
                index = (i + slides.length) % slides.length;
                slides.forEach(function (s, n) { s.classList.toggle('hidden', n !== index); });
                dots.forEach(function (d, n) {
                    d.classList.toggle('bg-white', n === index);
                    d.classList.toggle('bg-white/45', n !== index);
                });
            }
            function next() { show(index + 1); }
            function prev() { show(index - 1); }
            function start() { stop(); timer = setInterval(next, 5000); }
            function stop() { if (timer) clearInterval(timer); timer = null; }

            var prevBtn = document.getElementById('hero-prev');
            var nextBtn = document.getElementById('hero-next');
            if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
            if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });
            dots.forEach(function (d) {
                d.addEventListener('click', function () {
                    show(parseInt(d.getAttribute('data-hero-dot'), 10) || 0);
                    start();
                });
            });

            carousel.addEventListener('mouseenter', stop);
            carousel.addEventListener('mouseleave', start);

            var touchX = null;
            carousel.addEventListener('touchstart', function (e) {
                touchX = e.changedTouches[0].clientX;
                stop();
            }, { passive: true });
            carousel.addEventListener('touchend', function (e) {
                if (touchX === null) return;
                var dx = e.changedTouches[0].clientX - touchX;
                if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); }
                touchX = null;
                start();
            }, { passive: true });

            show(0);
            start();
        })();
    </script>
</body>
</html>
