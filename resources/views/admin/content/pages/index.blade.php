@extends('layouts.admin')

@section('title', 'Halaman Konten')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-navy-500">Halaman Konten</h1>
            <p class="mt-1 text-sm text-text-muted">Halaman statis dipublikasikan pada URL slug-nya.</p>
        </div>
        <x-button href="{{ route('office.pages.create') }}">+ Halaman Baru</x-button>
    </div>

    <div class="mt-5 overflow-x-auto rounded-xl border border-border bg-white">
        <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="bg-navy-500 text-white">
                <tr><th class="px-4 py-3">Judul</th><th class="px-4 py-3">URL</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Diperbarui</th><th class="px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($pages as $page)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $page->title }}</td>
                        <td class="px-4 py-3">/{{ $page->slug }}</td>
                        <td class="px-4 py-3">{{ $page->status === 'publish' ? 'Published' : 'Draft' }}</td>
                        <td class="px-4 py-3">{{ $page->updated_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3">
                                @if ($page->isPublished())
                                    <a class="text-navy-500 hover:underline" href="{{ route('content.show', $page->slug) }}" target="_blank" rel="noopener">Lihat</a>
                                @endif
                                <a class="text-navy-500 hover:underline" href="{{ route('office.pages.edit', $page) }}">Edit</a>
                                <form method="POST" action="{{ route('office.pages.destroy', $page) }}" onsubmit="return confirm('Hapus halaman ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-danger-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-text-muted">Belum ada halaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $pages->links() }}</div>
@endsection
