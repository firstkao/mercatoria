@php
    $footerGames = \App\Models\Game::active()->orderBy('name')->get(['name', 'slug']);
    $footerDevelopers = \App\Models\Developer::active()->orderBy('name')->get(['name', 'slug']);
    $footerPopular = \App\Models\Product::query()
        ->active()
        ->whereHas('games', fn ($query) => $query->active())
        ->with(['games', 'variants'])
        ->orderByDesc('sold_count')
        ->orderByDesc('id')
        ->limit(4)
        ->get();
    $footerPrices = app(\App\Services\Product\ProductPriceFormatter::class);
    $footerPages = [
        'blacklist' => 'Blacklist',
        'cara-belanja' => 'Cara Belanja',
        'kebijakan-privasi' => 'Kebijakan Privasi',
        'reseller' => 'Reseller',
        'faq' => 'Tanya Jawab Umum',
        'tentang-kami' => 'Tentang Kami',
    ];
    $social = config('mercatoria.store.social', []);
    $socialIcons = ['instagram' => '📷', 'facebook' => '📘', 'x' => '🐦', 'whatsapp' => '💬'];
    $socialLinks = [
        'instagram' => $social['instagram'] ?? '#',
        'facebook' => $social['facebook'] ?? '#',
        'x' => $social['x'] ?? '#',
        'whatsapp' => 'https://wa.me/'.config('mercatoria.store.wa_admin'),
    ];
    $marketplaces = ['TOCO' => setting('marketplace_toco_url', '#'), 'SHOPEE' => setting('marketplace_shopee_url', '#')];
    $linkClass = 'text-[12px] leading-[1.8] text-[#B0B3C5] hover:text-primary-300';
@endphp
<footer id="site-footer" class="mt-12 bg-[#1E1E2D] text-white">
    <div class="mx-auto grid max-w-[1200px] gap-8 px-4 py-14 md:grid-cols-2 md:px-6 lg:grid-cols-4 lg:px-8">
        <div data-footer-brand>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded bg-white text-sm font-black text-[#1E1E2D]">M</span>
                <span class="leading-tight">
                    <span class="block text-base font-bold tracking-wide">MERCATORIA</span>
                    <span class="block text-[9px] tracking-widest text-[#B0B3C5]">MERCH &amp; COLLECTIBLES STORE</span>
                </span>
            </a>
            <div class="mt-4 flex gap-3 text-lg">
                @foreach ($socialLinks as $name => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($name) }}">{{ $socialIcons[$name] }}</a>
                @endforeach
            </div>
            <div class="mt-4 flex gap-2">
                @foreach ($marketplaces as $label => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                        class="rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white hover:border-primary-300 hover:text-primary-300">{{ $label }} →</a>
                @endforeach
            </div>
        </div>

        <details data-footer-section class="group" open>
            <summary class="cursor-pointer list-none text-sm font-bold md:cursor-default">▸ Game <span class="text-[#B0B3C5] md:hidden">({{ $footerGames->count() }})</span></summary>
            <ul class="mt-3.5">
                @foreach ($footerGames as $footerGame)
                    <li><a href="{{ route('games.show', $footerGame->slug) }}" class="{{ $linkClass }}">{{ $footerGame->name }}</a></li>
                @endforeach
            </ul>
        </details>

        <div class="space-y-6">
            <details data-footer-section open>
                <summary class="cursor-pointer list-none text-sm font-bold md:cursor-default">▸ Developer <span class="text-[#B0B3C5] md:hidden">({{ $footerDevelopers->count() }})</span></summary>
                <ul class="mt-3.5">
                    @foreach ($footerDevelopers as $footerDeveloper)
                        <li><a href="{{ route('developers.show', $footerDeveloper->slug) }}" class="{{ $linkClass }}">{{ $footerDeveloper->name }}</a></li>
                    @endforeach
                </ul>
            </details>
            <details data-footer-section open>
                <summary class="cursor-pointer list-none text-sm font-bold md:cursor-default">▸ Jelajahi <span class="text-[#B0B3C5] md:hidden">({{ count($footerPages) }})</span></summary>
                <ul class="mt-3.5">
                    @foreach ($footerPages as $slug => $label)
                        <li><a href="{{ route('content.show', $slug) }}" class="{{ $linkClass }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </details>
        </div>

        <div>
            <h2 class="text-sm font-bold">Produk Terpopuler <span class="text-[#B0B3C5] md:hidden">(4 item)</span></h2>
            <ul class="mt-3.5">
                @forelse ($footerPopular as $popular)
                    @php($popularGame = $popular->games->first())
                    <li class="flex gap-3 border-b border-white/10 py-2 last:border-b-0">
                        <a href="{{ $popularGame ? route('games.products.show', [$popularGame->slug, $popular->slug]) : route('products.direct', $popular->slug) }}" class="flex gap-3">
                            @if ($popular->main_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($popular->main_image_path) }}" alt="" loading="lazy" class="h-12 w-12 shrink-0 object-cover" draggable="false">
                            @else
                                <span class="h-12 w-12 shrink-0 bg-white/10"></span>
                            @endif
                            <span class="min-w-0 text-[12px] leading-snug">
                                @if ($popularGame)<span class="block text-[10px] font-bold text-[#B0B3C5]">[{{ mb_strtoupper($popularGame->name) }}]</span>@endif
                                <span class="line-clamp-2 text-white">{{ $popular->name }}</span>
                                <span class="block font-bold text-primary-300">{{ $footerPrices->label($popular) }}</span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="text-[12px] text-[#B0B3C5]">Belum ada produk.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="border-t border-white/[0.08]">
        <div class="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-4 py-[18px] text-[11px] text-[#888] md:px-6 lg:px-8">
            <span>&copy; {{ date('Y') }} MERCATORIA</span>
            <span class="flex items-center gap-3">
                Powered by MERCATORIA
                <button type="button" id="footer-back-to-top" aria-label="Kembali ke atas"
                    class="rounded border border-white/25 px-2 py-0.5 text-white hover:border-primary-300 hover:text-primary-300">^</button>
            </span>
        </div>
    </div>
</footer>

@once
    @push('scripts')
        <script>
            (() => {
                document.getElementById('footer-back-to-top')?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
                const desktop = window.matchMedia('(min-width: 768px)');
                const sections = document.querySelectorAll('[data-footer-section]');
                const sync = () => sections.forEach(section => { section.open = desktop.matches; });
                sync();
                desktop.addEventListener('change', sync);
                sections.forEach(section => section.querySelector('summary').addEventListener('click', event => {
                    if (desktop.matches) event.preventDefault();
                }));
            })();
        </script>
    @endpush
@endonce
