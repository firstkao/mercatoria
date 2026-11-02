<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-head :seo="$seo" :json-ld="$jsonLd" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body id="top" class="flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    @include('frontend.partials._header')

    <main class="flex-1">
        {{-- Hero: gambar statis tanpa carousel JS (ringan untuk crawler) --}}
        <section id="hero" class="mx-auto max-w-[1200px] px-8 py-6">
            @foreach (($heroSlides ?? []) as $slide)
                <a href="{{ data_get($slide, 'url') ?? '#' }}" class="mb-3 block overflow-hidden rounded-lg border border-rule">
                    @if (data_get($slide, 'image'))
                        <img src="{{ data_get($slide, 'image') }}" alt="" class="aspect-[13/8] w-full object-cover">
                    @endif
                </a>
            @endforeach
        </section>

        {{-- Jelajahi Game Favoritmu --}}
        <section id="game-grid" class="mx-auto max-w-[1200px] px-8 pb-12">
            <h2 class="text-center font-display text-[26px] font-semibold text-ink">Jelajahi Game Favoritmu</h2>
            <p class="mt-1 text-center text-[13px] text-ink/60">Temukan merchandise eksklusif dari game favoritmu dan lihat koleksi lengkapnya di setiap kategori!</p>

            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                @foreach (($games ?? collect()) as $game)
                    <a href="{{ route('catalog.game', $game->slug) }}" class="group relative block aspect-square overflow-hidden rounded-lg bg-paper">
                        @if ($game->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($game->logo_path) }}" alt="{{ $game->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-[#F5F7FA]">
                                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-ink/35">{{ $game->name }}</span>
                            </div>
                        @endif
                        <div class="absolute bottom-2 left-1/2 w-[82%] -translate-x-1/2 rounded bg-white/92 px-2 py-1.5 text-center">
                            <p class="text-[10.5px] font-bold uppercase leading-tight text-ink">{{ $game->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- MERCH BARU --}}
        <section id="merch-baru" class="mx-auto max-w-[1200px] px-8 pb-14">
            <h2 class="text-center text-[18px] font-bold uppercase tracking-wide text-ink">MERCH BARU</h2>

            <div class="mt-5 grid grid-cols-2 gap-3.5 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7">
                @foreach (($products ?? collect()) as $product)
                    <a href="{{ route('product.show', [$product->game->slug, $product->slug]) }}" class="group block">
                        <div class="relative aspect-[5/4] overflow-hidden rounded bg-paper">
                            @if ($product->image_path)
                                <img src="{{ \App\Services\Image\ImageOptimizerService::url($product->image_path, 'thumb') }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @endif
                            @if ($product->tags->contains('slug', 'presale'))
                                <span class="absolute left-2 top-2 rounded bg-[#3986C6] px-2 py-[3px] text-[9px] font-bold uppercase text-white">PRESALE</span>
                            @elseif ($product->tags->contains('slug', 'limited'))
                                <span class="absolute left-2 top-2 rounded bg-[#E53935] px-2 py-[3px] text-[9px] font-bold uppercase text-white">LIMITED</span>
                            @endif
                        </div>
                        <p class="mt-1.5 text-[11.5px] leading-[1.35] text-ink">
                            <strong>[{{ strtoupper($product->game->name) }}]</strong> {{ $product->name }}
                        </p>
                        @if (!empty($product->price_min) && !empty($product->price_max))
                            <p class="mt-0.5 text-[11.5px]">
                                <span class="text-ink/45 line-through">{{ format_rp($product->price_min) }}</span>
                                <span class="text-ink/45"> – </span>
                                <strong class="text-[#0299E7]">{{ format_rp($product->price_max) }}</strong>
                            </p>
                        @elseif (!empty($product->price_idr))
                            <p class="mt-0.5 text-[11.5px] font-bold text-[#0299E7]">{{ format_rp($product->price_idr) }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    </main>

    @include('frontend.partials._footer')
</body>
</html>
