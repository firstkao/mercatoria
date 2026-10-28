<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Office — Mercatoria</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased">
    <header class="bg-black text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-semibold text-black">M</span>
                <span class="font-semibold tracking-wide">MERCATORIA OFFICE</span>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-white/30 px-3 py-1.5 hover:bg-white/10">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-10">
        <h1 class="text-2xl font-semibold">Dashboard Admin</h1>
        <p class="mt-2 text-gray-600">Panel admin menyusul pada bagian pembangunan berikutnya.</p>
    </main>
</body>
</html>
