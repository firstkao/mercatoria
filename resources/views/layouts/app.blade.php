<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trim($__env->yieldContent('title')) ?: ($seo['title'] ?? 'MERCATORIA') }}</title>
    @if (isset($seo))
        <meta name="description" content="{{ $seo['description'] }}">
        <link rel="canonical" href="{{ $seo['url'] }}">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta property="og:url" content="{{ $seo['url'] }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="{{ asset('js/app-modal.js') }}" defer></script>
    <x-ga4 />
</head>
<body id="top" class="flex min-h-screen flex-col bg-[#F5F7FA] font-sans text-ink antialiased">
    @include('frontend.partials._header')

    @if (session('status'))
        <div class="mx-auto w-full max-w-[1200px] px-4 pt-4">
            <div class="rounded-lg border border-rule bg-white px-4 py-3 text-sm text-ink">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="mx-auto w-full max-w-[1200px] flex-1 px-4 py-8">
        @yield('content')
    </main>

    @include('frontend.partials._footer')

    {{-- Back-to-top floating (muncul saat scroll > 400px) --}}
    <button id="back-to-top" type="button"
            class="fixed bottom-6 right-4 z-40 hidden h-11 w-11 items-center justify-center rounded-full border border-rule bg-white text-ink shadow-sm transition-colors hover:border-ink sm:right-6"
            aria-label="Kembali ke atas">
        <x-icons.chevron-up width="16" height="16" />
    </button>

    <script>
        (function () {
            var btn = document.getElementById('back-to-top');
            if (!btn) return;
            function onScroll() {
                if (window.scrollY > 400) {
                    btn.classList.remove('hidden');
                    btn.classList.add('flex');
                } else {
                    btn.classList.add('hidden');
                    btn.classList.remove('flex');
                }
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            btn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            onScroll();
        })();

        @if (\Illuminate\Support\Facades\Route::has('profile.notifications.unread-count'))
        (function () {
            var badge = document.querySelector('[data-unread]');
            if (!badge) return;
            setInterval(function () {
                fetch('{{ route("profile.notifications.unread-count") }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        var n = d.count ?? d.unread ?? 0;
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.style.display = n > 0 ? '' : 'none';
                    })
                    .catch(function () {});
            }, 60000);
        })();
        @endif
    </script>
</body>
</html>