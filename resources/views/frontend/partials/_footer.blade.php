<footer id="footer" class="bg-[#1E1E2D] text-white">
    <div class="mx-auto max-w-[1200px] px-8 py-14">
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

            {{-- Footer Game --}}
            <details class="group" open>
                <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                    Game
                    <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                </summary>
                <ul class="mt-3.5 text-[12px] leading-[1.8]">
                    @forelse (($footerGames ?? collect()) as $game)
                        <li><a href="{{ route('catalog.game', $game->slug) }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $game->name }}</a></li>
                    @empty
                        <li class="text-[#888]">Belum ada game</li>
                    @endforelse
                </ul>
            </details>

            <div class="space-y-8">
                {{-- Footer Developer --}}
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Developer
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                    </summary>
                    <ul class="mt-3.5 text-[12px] leading-[1.8]">
                        @forelse (($footerDevelopers ?? collect()) as $dev)
                            <li><a href="{{ route('catalog.developer', $dev->slug) }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $dev->name }}</a></li>
                        @empty
                            <li class="text-[#888]">Belum ada developer</li>
                        @endforelse
                    </ul>
                </details>

                {{-- Footer Jelajahi --}}
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Jelajahi
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden"><x-icons.chevron-up width="14" height="14" /></span>
                    </summary>
                    <ul class="mt-3.5 text-[12px] leading-[1.8]">
                        <li><a href="{{ route('page.blacklist') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Blacklist</a></li>
                        <li><a href="{{ route('page.cara-belanja') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Cara Belanja</a></li>
                        <li><a href="{{ route('page.kebijakan-privasi') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('page.reseller') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Reseller</a></li>
                        <li><a href="{{ route('page.faq') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tanya Jawab Umum</a></li>
                        <li><a href="{{ route('page.tentang-kami') }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">Tentang Kami</a></li>
                    </ul>
                </details>
            </div>

            {{-- Footer Produk Terpopuler --}}
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
        <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-8 py-[18px] text-[11px] text-[#888]">
            <p>&copy; 2022 MERCATORIA</p>
            <p>Powered by MERCATORIA</p>
        </div>
    </div>
</footer>
