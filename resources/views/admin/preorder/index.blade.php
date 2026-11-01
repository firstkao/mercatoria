@extends('admin.layouts.admin')

@section('title', 'Preorder Builder')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
            <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Preorder Builder</h1>
        </div>
        <a href="{{ route('office.preorder.create') }}" class="rounded-lg bg-black px-5 py-2.5 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Buat Halaman Preorder</a>
    </div>

    <div class="mt-6 space-y-3">
        @forelse ($pages as $page)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rule bg-white p-5">
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $page->title }}</p>
                    <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">/{{ $page->slug }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($page->status === 'publish')
                        <span class="rounded-full bg-[#E8F5E9] px-2.5 py-0.5 text-[10px] font-semibold text-[#2E7D32]">PUBLISH</span>
                    @else
                        <span class="rounded-full bg-paper px-2.5 py-0.5 text-[10px] font-semibold text-ink/45">DRAFT</span>
                    @endif
                    <a href="{{ route('office.preorder.edit', $page) }}" class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Edit</a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-12 text-center text-sm text-ink/45">
                Belum ada halaman preorder. Klik "+ Buat Halaman Preorder" untuk memulai.
            </div>
        @endforelse
    </div>
@endsection
