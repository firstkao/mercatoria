@extends('layouts.admin')

@section('title', 'Announcement Bar')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div><h1 class="text-2xl font-bold">Announcement Bar</h1><p class="text-sm text-text-muted">Pesan promo dismissible yang tampil sebelum navbar.</p></div>
    <a href="{{ route('office.announcements.create') }}" class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Tambah Announcement</a>
</div>
<div class="overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted"><tr><th class="px-4 py-3">Pesan</th><th class="px-4 py-3">Jadwal</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse ($announcements as $announcement)
                <tr>
                    <td class="px-4 py-3">{{ $announcement->text }}<br><span class="text-xs text-text-muted">{{ $announcement->link_url ?: '—' }}</span></td>
                    <td class="px-4 py-3">{{ $announcement->starts_at?->format('d-m-Y H:i') ?: '—' }} – {{ $announcement->ends_at?->format('d-m-Y H:i') ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $announcement->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="{{ route('office.announcements.edit', $announcement) }}" class="text-navy-500 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('office.announcements.destroy', $announcement) }}" class="inline" onsubmit="return confirm('Hapus announcement ini?')">
                            @csrf @method('DELETE')<button class="ml-3 text-danger-500 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-text-muted">Belum ada announcement.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $announcements->links() }}</div>
@endsection
