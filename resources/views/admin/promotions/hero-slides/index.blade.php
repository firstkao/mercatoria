@extends('layouts.admin')

@section('title', 'Hero Carousel')

@section('content')
<div class="mb-5 flex items-center justify-between">
    <div><h1 class="text-2xl font-bold">Hero Carousel</h1><p class="text-sm text-text-muted">Slide aktif akan tampil di halaman beranda.</p></div>
    <a href="{{ route('office.hero-slides.create') }}" class="rounded-lg bg-navy-500 px-4 py-2 text-sm font-medium text-white">Tambah Slide</a>
</div>
<div class="overflow-x-auto rounded-xl border border-border bg-white">
    <table class="min-w-full divide-y divide-border text-left text-sm">
        <thead class="bg-surface-2 text-xs uppercase text-text-muted"><tr><th class="px-4 py-3">Gambar</th><th class="px-4 py-3">Judul / Link</th><th class="px-4 py-3">Urutan</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
        <tbody class="divide-y divide-border">
            @forelse ($slides as $slide)
                <tr>
                    <td class="px-4 py-3"><img src="{{ Storage::disk('public')->url($slide->image_path) }}" alt="" class="h-14 w-28 rounded object-cover"></td>
                    <td class="px-4 py-3">{{ $slide->title ?: '—' }}<br><span class="text-xs text-text-muted">{{ $slide->link_url ?: '—' }}</span></td>
                    <td class="px-4 py-3">{{ $slide->sort_order }}</td><td class="px-4 py-3">{{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="{{ route('office.hero-slides.edit', $slide) }}" class="text-navy-500 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('office.hero-slides.destroy', $slide) }}" class="inline" onsubmit="return confirm('Hapus slide ini?')">
                            @csrf @method('DELETE')<button class="ml-3 text-danger-500 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-text-muted">Belum ada slide.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $slides->links() }}</div>
@endsection
