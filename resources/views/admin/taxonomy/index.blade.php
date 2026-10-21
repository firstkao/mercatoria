@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold text-navy-500">{{ $title }}</h1>
    <form method="GET" class="flex gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari {{ strtolower($title) }}..." class="rounded-lg border border-border px-3 py-2 text-sm">
        <button class="rounded-lg border border-border bg-white px-3 py-2 text-sm">🔍</button>
    </form>
</div>

<form method="POST" action="{{ route('office.'.$type.'.store') }}" class="mt-4 flex flex-wrap items-start gap-2 rounded-xl border border-border bg-white p-4">
    @csrf
    <div>
        <label for="name" class="sr-only">Nama {{ $title }}</label>
        <input id="name" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Nama {{ $title }} baru"
               class="w-64 max-w-full rounded-lg border border-border px-3 py-2 text-sm">
        @error('name')<p class="mt-1 text-sm text-danger-500">{{ $message }}</p>@enderror
    </div>
    <x-button type="submit">+ Tambah</x-button>
</form>

<div class="mt-4 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted">
            <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">{{ $countLabel }}</th><th class="px-4 py-3 text-right">Aksi</th></tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($items as $item)
                <tr class="hover:bg-[#F5F7FA]">
                    <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                    <td class="px-4 py-3 text-text-muted">{{ $item->slug }}</td>
                    <td class="px-4 py-3">{{ $item->products_count ?? $item->games_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('office.'.$type.'.destroy', $item->id) }}" onsubmit="return confirm('Hapus {{ $title }} ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-danger-500 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-text-muted">Belum ada data {{ strtolower($title) }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
