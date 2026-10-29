@extends('admin.layouts.admin')

@section('title', 'Halaman CMS')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Halaman CMS</h1>

    <form method="POST" action="{{ route('office.pages.store') }}" class="mt-4 flex flex-wrap items-end gap-2 rounded-xl border border-rule bg-white p-4">
        @csrf
        <input type="text" name="title" placeholder="Judul" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="slug" placeholder="slug-halaman" required class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <input type="text" name="content" placeholder="Isi halaman" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
        <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
        <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-ink">+ Tambah</button>
    </form>

    <div class="mt-4 space-y-3">
        @foreach ($pages as $page)
            <div class="rounded-xl border border-rule bg-white p-4">
                <form method="POST" action="{{ route('office.pages.update', $page) }}" class="flex flex-wrap items-end gap-2">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $page->title }}" class="rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <span class="py-2 font-mono text-xs text-ink/50">/{{ $page->slug }}</span>
                    <input type="text" name="content" value="{{ $page->content }}" class="flex-1 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" @checked($page->is_active) class="rounded border-rule"> Aktif</label>
                    <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Simpan</button>
                </form>

                @if (\Illuminate\Support\Facades\Route::has('office.pages.destroy'))
                    <div class="mt-2 flex justify-end">
                        <button type="button" class="rounded-lg border border-danger/40 px-3 py-1.5 text-xs font-semibold text-danger transition-colors hover:border-danger"
                                onclick="document.getElementById('del-{{ $page->id }}').showModal()">
                            Hapus
                        </button>
                    </div>
                    <dialog id="del-{{ $page->id }}" class="rounded-xl border border-rule p-0">
                        <form method="POST" action="{{ route('office.pages.destroy', $page) }}" class="p-5">
                            @csrf @method('DELETE')
                            <p class="text-sm font-semibold text-ink">Hapus halaman &ldquo;{{ $page->title }}&rdquo;?</p>
                            <p class="mt-1 text-xs text-ink/60">Tindakan ini tidak dapat dibatalkan.</p>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink"
                                        onclick="document.getElementById('del-{{ $page->id }}').close()">Batal</button>
                                <button type="submit" class="rounded-lg bg-[#E53935] px-3 py-1.5 text-xs font-semibold text-white">Hapus Permanen</button>
                            </div>
                        </form>
                    </dialog>
                @endif
            </div>
        @endforeach
    </div>
@endsection
