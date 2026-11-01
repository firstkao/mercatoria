@extends('admin.layouts.admin')

@php($isEditing = isset($preorderPage))

@section('title', $isEditing ? 'Edit Halaman Preorder' : 'Buat Halaman Preorder')

@section('content')
    <div class="mx-auto max-w-6xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
        <h1 class="mt-2 font-display text-[1.75rem] font-semibold text-ink">{{ $isEditing ? 'Edit: '.$preorderPage->title : 'Buat Halaman Preorder' }}</h1>

        <form method="POST" action="{{ $isEditing ? route('office.preorder.update', $preorderPage) : route('office.preorder.store') }}" id="preorder-form" class="mt-8 space-y-6 rounded-xl border border-rule bg-white p-8">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Judul Halaman <span class="text-danger">*</span></label>
                    <input type="text" name="title" required maxlength="150" value="{{ old('title', $preorderPage->title ?? '') }}" class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">
                </div>
                <div>
                    <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" required maxlength="170" value="{{ old('slug', $preorderPage->slug ?? 'preorder-baru') }}" class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Meta Deskripsi</label>
                    <textarea name="meta_description" rows="2" maxlength="300" class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">{{ old('meta_description', $preorderPage->meta_description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Status</label>
                    <select name="status" required class="mt-2 rounded-lg border border-rule px-3 py-3 text-sm text-ink">
                        <option value="draft" @selected(old('status', $preorderPage->status ?? 'draft') === 'draft')>Draft</option>
                        <option value="publish" @selected(old('status', $preorderPage->status ?? 'draft') === 'publish')>Publish</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-2 block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Builder</label>
                <div class="grid gap-4 lg:grid-cols-[220px_1fr]">
                    <div id="gjs-blocks" class="max-h-[650px] overflow-y-auto rounded-lg border border-rule bg-paper p-2"></div>
                    <div id="gjs" class="min-h-[650px] rounded-lg border border-rule"></div>
                </div>
                <input type="hidden" name="content" id="content-field" value="{{ old('content', $preorderPage->content ?? '') }}">
            </div>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-black px-8 py-3 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN HALAMAN</button>
                <button type="button" onclick="previewContent()" class="rounded-lg border border-rule px-8 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">Preview</button>
                <a href="{{ route('office.preorder.index') }}" class="text-sm text-link underline decoration-link/35 underline-offset-4">Kembali ke daftar</a>
            </div>
        </form>
    </div>

    <dialog id="preview-dialog" class="m-auto max-w-4xl rounded-xl border border-rule p-0">
        <div class="p-6">
            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Preview</p>
            <div id="preview-body" class="mt-4 max-h-[70vh] overflow-y-auto rounded-lg border border-rule bg-white p-6 text-sm leading-relaxed text-ink"></div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="document.getElementById('preview-dialog').close()" class="rounded-lg border border-rule px-5 py-2 text-xs font-semibold text-ink">Tutup</button>
            </div>
        </div>
    </dialog>

    @vite('resources/js/cms-editor.js')
@endsection
