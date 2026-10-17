@extends('layouts.admin')

@section('title', 'Game')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Game</h1>
        <p class="mt-1 text-sm text-text-muted">Kelola game, developer, dan default store ongkir.</p>
    </div>
    <x-button href="{{ route('office.games.create') }}">+ Tambah Game</x-button>
</div>

@if ($games->isEmpty())
    <div class="mt-5 rounded-xl border border-dashed border-border bg-white px-6 py-12 text-center">
        <h2 class="text-lg font-semibold">Belum ada game</h2>
        <x-button href="{{ route('office.games.create') }}" class="mt-4">Tambah Game</x-button>
    </div>
@else
    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr>
                    <th scope="col" class="px-4 py-3">Game</th>
                    <th scope="col" class="px-4 py-3">Default Store</th>
                    <th scope="col" class="px-4 py-3">Produk</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($games as $game)
                    <tr class="hover:bg-surface-2">
                        <th scope="row" class="px-4 py-4">
                            <span class="block font-semibold">{{ $game->name }}</span>
                            <span class="text-xs text-text-muted">/{{ $game->slug }}</span>
                        </th>
                        <td class="px-4 py-4">{{ $game->defaultStore?->name ?? '—' }}</td>
                        <td class="px-4 py-4">{{ $game->products_count }}</td>
                        <td class="px-4 py-4">{{ $game->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-4"><a href="{{ route('office.games.edit', $game) }}" class="font-medium text-navy-500 hover:underline">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $games->links() }}</div>
@endif
@endsection
