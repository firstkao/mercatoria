@extends('admin.layouts.admin')

@section('title', 'Halaman CMS')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Halaman CMS</h1>
        </div>
        <a href="{{ route('office.pages.create') }}" class="rounded-lg bg-black px-5 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Buat Halaman Baru</a>
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($pages as $page)
            <div class="rounded-xl border border-rule bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ $page->title }}</p>
                        <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">/{{ $page->slug }}</p>
                        @if ($page->meta_description)
                            <p class="mt-1 text-xs text-ink/50">{{ \Illuminate\Support\Str::limit($page->meta_description, 100) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($page->status === 'publish')
                            <span class="rounded-full bg-[#E8F5E9] px-2.5 py-0.5 text-[10px] font-semibold text-[#2E7D32]">PUBLISH</span>
                        @else
                            <span class="rounded-full bg-paper px-2.5 py-0.5 text-[10px] font-semibold text-ink/45">DRAFT</span>
                        @endif
                        <a href="{{ route('office.pages.edit', $page) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Edit</a>
                        <button type="button" onclick="document.getElementById('cms-del-{{ $page->id }}').showModal()"
                                class="text-sm font-medium text-danger hover:underline">Hapus</button>
                    </div>
                </div>

                <dialog id="cms-del-{{ $page->id }}" class="m-auto rounded-xl border border-rule p-0">
                    <form method="POST" action="{{ route('office.pages.destroy', $page) }}" class="p-6">
                        @csrf @method('DELETE')
                        <p class="text-sm font-semibold text-ink">Hapus halaman &ldquo;{{ $page->title }}&rdquo;?</p>
                        <p class="mt-1 text-xs text-ink/55">Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" class="rounded-lg border border-rule px-4 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('cms-del-{{ $page->id }}').close()">Batal</button>
                            <button type="submit" class="rounded-lg bg-danger px-4 py-1.5 text-xs font-semibold text-white">Hapus</button>
                        </div>
                    </form>
                </dialog>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-12 text-center text-sm text-ink/45">
                Belum ada halaman CMS. Klik "+ Buat Halaman Baru" untuk memulai.
            </div>
        @endforelse
    </div>
@endsection
