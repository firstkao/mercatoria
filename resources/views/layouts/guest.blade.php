<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MERCATORIA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=IBM+Plex+Mono:wght@400;500;600&family=Schibsted+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="{{ asset('js/app-modal.js') }}" defer></script>
    <x-ga4 />
</head>
<body id="top" class="flex min-h-screen flex-col font-sans text-ink antialiased">
    @include('frontend.partials._header')

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

    @include('frontend.partials._footer')
</body>
</html>
