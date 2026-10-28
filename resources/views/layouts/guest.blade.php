<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mercatoria')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="top" class="flex min-h-screen flex-col font-sans text-ink antialiased">
    {{-- Header (Design Spec 4.4.1 — logo tengah: M + MERCATORIA + sub-teks) --}}
    <header class="border-b border-[#EAEAEA] bg-white">
        <div class="mx-auto flex min-h-[72px] max-w-[1200px] items-center justify-center px-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 no-underline">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-black text-lg font-semibold text-white">M</span>
                <span class="flex flex-col leading-none">
                    <span class="text-[17px] font-semibold tracking-[0.14em] text-ink">MERCATORIA</span>
                    <span class="mt-1 font-mono text-[9px] uppercase tracking-[0.22em] text-ink/50">Merch &amp; Collectibles Store</span>
                </span>
            </a>
        </div>
    </header>

    <main class="flex flex-1 items-start justify-center px-4 py-8 sm:py-10">
        <div class="@yield('width', 'w-full max-w-md')">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg border border-danger/35 bg-white px-4 py-3 text-sm text-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer (Design Spec 4.4.5 — dark navy 4.4.5 / copyright bar) --}}
    <footer class="bg-[#1E1E2D] text-white">
        <div class="mx-auto max-w-[1200px] px-4 py-14">
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                {{-- Kolom 1: Brand & Marketplace --}}
                <div>
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-lg font-semibold text-[#1E1E2D]">M</span>
                        <span class="flex flex-col leading-none">
                            <span class="text-[17px] font-semibold tracking-[0.14em] text-white">MERCATORIA</span>
                            <span class="mt-1 font-mono text-[9px] uppercase tracking-[0.22em] text-[#B0B3C5]">Merch &amp; Collectibles Store</span>
                        </span>
                    </div>

                    <div class="mt-5 flex items-center gap-4">
                        <a href="#" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Instagram">
                            <x-icons.instagram width="18" height="18" />
                        </a>
                        <a href="#" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="Facebook">
                            <x-icons.facebook width="18" height="18" />
                        </a>
                        <a href="#" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="X">
                            <x-icons.twitter-x width="18" height="18" />
                        </a>
                        <a href="#" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]" aria-label="WhatsApp">
                            <x-icons.whatsapp width="18" height="18" />
                        </a>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="#" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            TOCO
                            <x-icons.arrow-right width="12" height="12" />
                        </a>
                        <a href="#" class="inline-flex items-center gap-1.5 rounded-[20px] border border-white/25 px-4 py-1.5 text-[11px] font-bold text-white transition-colors hover:border-white/50">
                            SHOPEE
                            <x-icons.arrow-right width="12" height="12" />
                        </a>
                    </div>
                </div>

                {{-- Kolom 2: Game --}}
                <details class="group" open>
                    <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                        Game
                        <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                            <x-icons.chevron-up width="14" height="14" />
                        </span>
                    </summary>
                    <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                        @foreach ([
                            'Aether Gazer' => '/aether-gazer',
                            'Arknights' => '/arknights',
                            'Arknights: Endfield' => '/arknights-endfield',
                            'Azur Lane' => '/azur-lane',
                            'Beyond The World' => '/beyond-the-world',
                            'Duet Night Abyss' => '/duet-night-abyss',
                            'Genshin Impact' => '/genshin-impact',
                            'Honkai Impact 3' => '/honkai-impact-3',
                            'Honkai: Star Rail' => '/honkai-star-rail',
                            'Light and Night' => '/light-and-night',
                            'Love and Deepspace' => '/love-and-deepspace',
                            'Lovebrush Chronicles' => '/lovebrush-chronicles',
                            'Mercatoria' => '/mercatoria',
                            "Mr Love: Queen's Choice" => '/mr-love-queens-choice',
                            'Path to Nowhere' => '/path-to-nowhere',
                            'Punishing: Gray Raven' => '/punishing-gray-raven',
                            'Reverse: 1999' => '/reverse-1999',
                            'Tears of Themis' => '/tears-of-themis',
                            'Where Winds Meet' => '/where-winds-meet',
                            'Wuthering Waves' => '/wuthering-waves',
                            'Zenless Zone Zero' => '/zenless-zone-zero',
                        ] as $label => $href)
                            <li>
                                <a href="{{ $href }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </details>

                {{-- Kolom 3: Developer & Jelajahi --}}
                <div class="space-y-8">
                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Developer
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <x-icons.chevron-up width="14" height="14" />
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            @foreach ([
                                'Aisno Games' => '/developer/aisno-games',
                                'BluePoch' => '/developer/bluepoch',
                                'HyperGryph' => '/developer/hypergryph',
                                'Kuro Games' => '/developer/kuro-games',
                                'Manjuu Game' => '/developer/manjuu-game',
                                'miHoYo' => '/developer/mihoyo',
                                'NetEase Games' => '/developer/netease-games',
                                'Papergames' => '/developer/papergames',
                                'Tencent Games' => '/developer/tencent-games',
                                'Yongshi Technology' => '/developer/yongshi-technology',
                            ] as $label => $href)
                                <li>
                                    <a href="{{ $href }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </details>

                    <details class="group" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-[14px] font-bold text-white marker:hidden lg:pointer-events-none lg:cursor-default">
                            Jelajahi
                            <span class="text-[#B0B3C5] transition-transform group-open:rotate-180 lg:hidden">
                                <x-icons.chevron-up width="14" height="14" />
                            </span>
                        </summary>
                        <ul class="mt-3.5 space-y-0 text-[12px] leading-[1.8]">
                            @foreach ([
                                'Blacklist' => '/blacklist',
                                'Cara Belanja' => '/cara-belanja',
                                'Kebijakan Privasi' => '/kebijakan-privasi',
                                'Reseller' => '/reseller',
                                'Tanya Jawab Umum' => '/tanya-jawab-umum',
                                'Tentang Kami' => '/tentang-kami',
                            ] as $label => $href)
                                <li>
                                    <a href="{{ $href }}" class="text-[#B0B3C5] transition-colors hover:text-[#4FC3F7]">{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        {{-- Bottom Copyright Bar (4.4.5) --}}
        <div class="border-t border-white/[0.08]">
            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-[18px] text-[11px] text-[#888]">
                <p>&copy; 2026 MERCATORIA</p>
                <div class="flex items-center gap-4">
                    <p>Powered by MERCATORIA</p>
                    <a href="#top" class="inline-flex h-7 w-7 items-center justify-center rounded border border-white/15 text-[#B0B3C5] transition-colors hover:border-white/35 hover:text-white" aria-label="Kembali ke atas">
                        <x-icons.chevron-up width="12" height="12" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
