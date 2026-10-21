@extends('layouts.admin')

@section('title', 'Customer')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-navy-500">Customer</h1>
        <p class="mt-1 text-sm text-text-muted">Kelola akun customer dan migrasi customer lama.</p>
    </div>
    <x-button href="{{ route('office.users.create') }}">+ Migrasi Customer Lama</x-button>
    <a href="{{ route('office.spammers.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold text-navy-500">Kelola Spammer</a>
</div>

<form method="GET" action="{{ route('office.users.index') }}" class="mt-5 flex gap-3 rounded-xl border border-border bg-white p-4">
    <label class="sr-only" for="customer-search">Cari nama, email, atau HP</label>
    <input id="customer-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
           placeholder="Cari nama, email, atau HP..." class="min-w-0 flex-1 rounded-lg border-border text-sm">
    <x-button type="submit" variant="ghost">Cari</x-button>
</form>

<div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
    <table class="w-full min-w-[800px] text-left text-sm">
        <thead class="bg-navy-500 text-white">
            <tr>
                <th class="px-4 py-3">Customer</th>
                <th class="px-4 py-3">No. HP</th>
                <th class="px-4 py-3">Status Email</th>
                <th class="px-4 py-3">Status Profil</th>
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
                    <td class="px-4 py-3">{{ $user->tgl_lahir ? 'Lengkap' : 'Belum lengkap' }}</td>
                    <td class="px-4 py-3">
                        @if ($user->isMigrated())
                            <span class="rounded-full bg-[#E3F2FD] px-2 py-1 text-xs font-semibold text-[#1E88E5]">🔄 Migrated</span>
                        @else
                            Registrasi baru
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('office.users.show', $user) }}" class="font-medium text-navy-500 hover:underline">Detail</a>
                            @if ($user->blocked_spammer)
                                <form method="POST" action="{{ route('office.users.forever-delete', $user) }}"
                                      onsubmit="return confirm('Hapus akun spammer permanen? Identitas blokir akan tetap disimpan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-danger-500 hover:underline">Hapus spammer permanen</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-text-muted">Belum ada customer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $users->links() }}</div>
@endsection
