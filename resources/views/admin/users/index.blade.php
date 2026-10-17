@extends('layouts.admin')

@section('title', 'Customer')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Customer</h1>
        <p class="mt-1 text-sm text-text-muted">Kelola akun customer dan migrasi customer lama.</p>
    </div>
    <x-button href="{{ route('office.users.create') }}">+ Migrasi Customer Lama</x-button>
</div>

<form method="GET" action="{{ route('office.users.index') }}" class="mt-5 flex gap-3 rounded-xl border border-border bg-white p-4">
    <label class="sr-only" for="customer-search">Cari nama, email, atau HP</label>
    <input id="customer-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
           placeholder="Cari nama, email, atau HP..." class="min-w-0 flex-1 rounded-lg border-border text-sm">
    <x-button type="submit" variant="ghost">Cari</x-button>
</form>

<div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="w-full min-w-[720px] text-left text-sm">
        <thead class="bg-navy-500 text-white">
            <tr>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">No. HP</th>
                <th class="px-4 py-3">Status Email</th>
                <th class="px-4 py-3">Asal</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($users as $user)
                <tr>
                    <th scope="row" class="px-4 py-3">
                        <span class="block font-semibold">{{ $user->name }}</span>
                        <span class="text-xs text-text-muted">{{ $user->email }}</span>
                    </th>
                    <td class="px-4 py-3">{{ $user->no_hp ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $user->hasVerifiedEmail() ? 'Terverifikasi' : 'Belum verifikasi' }}</td>
                    <td class="px-4 py-3">{{ $user->isMigrated() ? 'Migrasi lama' : 'Registrasi baru' }}</td>
                    <td class="px-4 py-3"><a href="{{ route('office.users.show', $user) }}" class="font-medium text-navy-500 hover:underline">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-text-muted">Belum ada customer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $users->links() }}</div>
@endsection
