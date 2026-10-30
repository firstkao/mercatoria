@extends('admin.layouts.admin')

@section('title', 'Halaman CMS')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <style>
        .ql-toolbar.ql-snow { border-color: #D8D2C4; border-radius: 0.5rem 0.5rem 0 0; background: #F2EFE8; }
        .ql-container.ql-snow { border-color: #D8D2C4; border-radius: 0 0 0.5rem 0.5rem; font-family: 'Schibsted Grotesk', sans-serif; font-size: 14px; color: #141B22; min-height: 200px; }
        .ql-editor { min-height: 200px; }
        .ql-editor h1 { font-size: 1.5rem; font-weight: 600; margin: 0.5em 0; }
        .ql-editor h2 { font-size: 1.25rem; font-weight: 600; margin: 0.5em 0; }
        .ql-editor h3 { font-size: 1.1rem; font-weight: 600; margin: 0.5em 0; }
        .ql-editor blockquote { border-left: 3px solid #D8D2C4; padding-left: 1em; color: #141B22; opacity: 0.75; }
        .ql-snow .ql-stroke { stroke: #141B22; }
        .ql-snow .ql-fill { fill: #141B22; }
        .ql-snow .ql-picker { color: #141B22; }
    </style>

    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Halaman CMS</h1>
    <p class="mt-1 text-sm text-ink/55">Buat dan kelola halaman statis dengan rich text editor.</p>

    {{-- Form tambah halaman --}}
    <form method="POST" action="{{ route('office.pages.store') }}" id="cms-create-form" class="mt-4 rounded-xl border border-rule bg-white p-4">
        @csrf
        <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Halaman Baru</p>
        <div class="mt-3 flex flex-wrap gap-2">
            <input type="text" name="title" placeholder="Judul halaman" required class="w-64 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <input type="text" name="slug" placeholder="slug-halaman" required class="w-56 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif</label>
        </div>
        <input type="hidden" name="content" id="cms-create-content">
        <div id="cms-create-editor" class="mt-2"></div>
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-black px-5 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN</button>
            <button type="button" onclick="cmsPreview('create')" class="rounded-lg border border-rule px-5 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Preview</button>
        </div>
    </form>

    {{-- Daftar halaman --}}
    <div class="mt-5 space-y-3">
        @forelse ($pages as $page)
            <div class="rounded-xl border border-rule bg-white p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ $page->title }}</p>
                        <p class="mt-0.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">/{{ $page->slug }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($page->is_active)
                            <span class="rounded-full bg-[#E8F5E9] px-2.5 py-0.5 text-[10px] font-semibold text-[#2E7D32]">AKTIF</span>
                        @else
                            <span class="rounded-full bg-paper px-2.5 py-0.5 text-[10px] font-semibold text-ink/45">NONAKTIF</span>
                        @endif
                        <button type="button" onclick="cmsEdit({{ $page->id }}, @js($page->title), @js($page->content), {{ $page->is_active ? 'true' : 'false' }})"
                                class="text-sm font-medium text-link underline decoration-link/35 underline-offset-4">Edit</button>
                        <button type="button" onclick="document.getElementById('cms-del-{{ $page->id }}').showModal()"
                                class="text-sm font-medium text-danger hover:underline">Hapus</button>
                    </div>
                </div>

                <dialog id="cms-del-{{ $page->id }}" class="m-auto rounded-xl border border-rule p-0">
                    <form method="POST" action="{{ route('office.pages.destroy', $page) }}" class="p-5">
                        @csrf @method('DELETE')
                        <p class="text-sm font-semibold text-ink">Hapus halaman &ldquo;{{ $page->title }}&rdquo;?</p>
                        <p class="mt-1 text-xs text-ink/55">Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="mt-3 flex justify-end gap-2">
                            <button type="button" class="rounded-lg border border-rule px-3 py-1.5 text-xs font-semibold text-ink" onclick="document.getElementById('cms-del-{{ $page->id }}').close()">Batal</button>
                            <button type="submit" class="rounded-lg bg-danger px-3 py-1.5 text-xs font-semibold text-white">Hapus</button>
                        </div>
                    </form>
                </dialog>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">Belum ada halaman CMS.</div>
        @endforelse
    </div>

    {{-- Modal edit halaman --}}
    <dialog id="cms-edit-dialog" class="m-auto max-w-2xl rounded-xl border border-rule p-0">
        <form method="POST" id="cms-edit-form" class="p-5">
            @csrf @method('PUT')
            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Edit Halaman</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <input type="text" name="title" id="cms-edit-title" required class="w-64 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                <label class="flex items-center gap-1 py-2 text-sm text-ink/80"><input type="checkbox" name="is_active" id="cms-edit-active" value="1" class="rounded border-rule"> Aktif</label>
            </div>
            <input type="hidden" name="content" id="cms-edit-content">
            <div id="cms-edit-editor" class="mt-2"></div>
            <div class="mt-3 flex items-center gap-2">
                <button type="submit" class="rounded-lg bg-black px-5 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">SIMPAN</button>
                <button type="button" onclick="cmsPreview('edit')" class="rounded-lg border border-rule px-5 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Preview</button>
                <button type="button" onclick="document.getElementById('cms-edit-dialog').close()" class="ml-auto text-xs text-ink/55 hover:text-ink">Tutup</button>
            </div>
        </form>
    </dialog>

    {{-- Modal preview --}}
    <dialog id="cms-preview-dialog" class="m-auto max-w-2xl rounded-xl border border-rule p-0">
        <div class="p-5">
            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-ink">Preview</p>
            <div id="cms-preview-body" class="mt-3 max-h-[60vh] overflow-y-auto rounded-lg border border-rule bg-paper p-4 text-sm text-ink"></div>
            <div class="mt-3 flex justify-end">
                <button type="button" onclick="document.getElementById('cms-preview-dialog').close()" class="rounded-lg border border-rule px-4 py-1.5 text-xs font-semibold text-ink">Tutup</button>
            </div>
        </div>
    </dialog>

    <script>
        (function () {
            var toolbar = [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                ['link', 'image'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote'],
                ['clean']
            ];

            window._cmsCreate = new Quill('#cms-create-editor', { theme: 'snow', modules: { toolbar: toolbar } });
            window._cmsEdit = new Quill('#cms-edit-editor', { theme: 'snow', modules: { toolbar: toolbar } });

            document.getElementById('cms-create-form').addEventListener('submit', function () {
                document.getElementById('cms-create-content').value = window._cmsCreate.root.innerHTML;
            });

            window.cmsEdit = function (id, title, content, active) {
                document.getElementById('cms-edit-form').action = '{{ url("office/pages") }}/' + id;
                document.getElementById('cms-edit-title').value = title;
                document.getElementById('cms-edit-active').checked = active;
                window._cmsEdit.root.innerHTML = content || '';
                document.getElementById('cms-edit-dialog').showModal();
            };

            document.getElementById('cms-edit-form').addEventListener('submit', function () {
                document.getElementById('cms-edit-content').value = window._cmsEdit.root.innerHTML;
            });

            window.cmsPreview = function (which) {
                var html = which === 'create' ? window._cmsCreate.root.innerHTML : window._cmsEdit.root.innerHTML;
                document.getElementById('cms-preview-body').innerHTML = html;
                document.getElementById('cms-preview-dialog').showModal();
            };
        })();
    </script>
@endsection
