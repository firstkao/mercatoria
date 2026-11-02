@extends('admin.layouts.admin')

@section('title', 'Edit Halaman CMS')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        .ql-toolbar.ql-snow { border-color: #D8D2C4; border-radius: 0.5rem 0.5rem 0 0; background: #F2EFE8; }
        .ql-container.ql-snow { border-color: #D8D2C4; border-radius: 0 0 0.5rem 0.5rem; font-family: 'Schibsted Grotesk', sans-serif; font-size: 14px; color: #141B22; }
        .ql-editor { min-height: 320px; padding: 1.25rem; }
        .ql-editor h1 { font-size: 1.5rem; font-weight: 600; margin: 0.75em 0; }
        .ql-editor h2 { font-size: 1.25rem; font-weight: 600; margin: 0.75em 0; }
        .ql-editor h3 { font-size: 1.1rem; font-weight: 600; margin: 0.75em 0; }
        .ql-editor blockquote { border-left: 3px solid #D8D2C4; padding-left: 1em; color: #141B22; opacity: 0.75; margin: 1em 0; }
        .ql-editor pre { background: #F2EFE8; border-radius: 0.375rem; padding: 0.75em; font-size: 13px; }
        .ql-snow .ql-stroke { stroke: #141B22; }
        .ql-snow .ql-fill { fill: #141B22; }
        .ql-snow .ql-picker { color: #141B22; }
    </style>

    <div class="mx-auto max-w-3xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
        <h1 class="mt-2 font-display text-[1.75rem] font-semibold text-ink">Edit: {{ $page->title }}</h1>
        <p class="mt-2 font-mono text-xs text-ink/45">/{{ $page->slug }}</p>

        <form method="POST" action="{{ route('office.pages.update', $page) }}" id="page-form" class="mt-8 space-y-6 rounded-xl border border-rule bg-white p-8">
            @csrf @method('PUT')
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Judul Halaman <span class="text-danger">*</span></label>
                <input type="text" name="title" required maxlength="150" value="{{ old('title', $page->title) }}"
                       class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Slug <span class="text-danger">*</span></label>
                <input type="text" name="slug" required maxlength="170" value="{{ old('slug', $page->slug) }}"
                       class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Meta Deskripsi</label>
                <textarea name="meta_description" rows="2" maxlength="300" placeholder="Deskripsi untuk SEO (maks 300 karakter)"
                          class="mt-2 w-full rounded-lg border border-rule px-4 py-3 text-sm text-ink">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Konten</label>
                <input type="hidden" name="content" id="content-field" value="{{ old('content', $page->content) }}">
                <div id="editor" class="mt-2"></div>
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Status</label>
                <select name="status" required class="mt-2 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
                    <option value="publish" @selected(old('status', $page->status) === 'publish')>Publish</option>
                </select>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-black px-8 py-3 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN PERUBAHAN</button>
                <button type="button" onclick="previewContent()" class="rounded-lg border border-rule px-8 py-3 text-sm font-semibold text-ink transition-colors hover:border-ink">Preview</button>
                <a href="{{ route('office.pages.index') }}" class="ml-auto text-sm text-link underline decoration-link/35 underline-offset-4">Kembali ke daftar</a>
            </div>
        </form>
    </div>

    <dialog id="preview-dialog" class="m-auto w-[calc(100%-2rem)] max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-rule p-0 shadow-2xl backdrop:bg-black/50">
        <div class="p-6">
            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Preview</p>
            <div id="preview-body" class="mt-4 max-h-[60vh] overflow-y-auto rounded-lg border border-rule bg-paper p-6 text-sm leading-relaxed text-ink"></div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="document.getElementById('preview-dialog').close()" class="rounded-lg border border-rule px-5 py-2 text-xs font-semibold text-ink">Tutup</button>
            </div>
        </div>
    </dialog>

    <script>
        (function () {
            window._quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'image'],
                        [{ align: [] }],
                        ['clean']
                    ]
                }
            });
            window._quill.root.innerHTML = document.getElementById('content-field').value;
            document.getElementById('page-form').addEventListener('submit', function () {
                document.getElementById('content-field').value = window._quill.root.innerHTML;
            });
            window.previewContent = function () {
                document.getElementById('preview-body').innerHTML = window._quill.root.innerHTML;
                document.getElementById('preview-dialog').showModal();
            };
        })();
    </script>
@endsection
