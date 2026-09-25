@php($isNew = ! $page->exists)
@extends('admin.layouts.app', ['title' => $isNew ? 'Tambah halaman' : 'Edit halaman', 'back' => route('admin.pages.index')])

@section('actions')
    <button type="submit" form="page-form" class="btn btn--primary only-desktop">Simpan</button>
@endsection

@push('head')
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">
@endpush

@section('content')
    <form id="page-form" method="POST" action="{{ $isNew ? route('admin.pages.store') : route('admin.pages.update', $page) }}" class="form-grid" novalidate>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <div class="form-grid__main">
            <section class="panel stack">
                <label class="field">
                    <span>Judul</span>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" maxlength="255" required>
                    @include('admin.partials.error', ['name' => 'title'])
                </label>

                <label class="field">
                    <span>Alamat</span>
                    <div class="input-prefix">
                        <span>mercatoria.id/</span>
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" maxlength="255" placeholder="otomatis dari judul" @readonly($page->is_system)>
                    </div>
                    @if ($page->is_system)
                        <small class="hint">Alamat halaman sistem tidak bisa diubah karena dipakai di formulir daftar dan reseller.</small>
                    @endif
                    @include('admin.partials.error', ['name' => 'slug'])
                </label>

                <div class="field">
                    <span>Isi</span>
                    {{-- The textarea stays as a fallback if the editor script cannot load. --}}
                    <textarea name="content" id="page-content" rows="24" class="mono-area" data-editor-source>{{ old('content', $page->content) }}</textarea>
                    <div class="editor" data-editor hidden></div>
                    @include('admin.partials.error', ['name' => 'content'])
                </div>
            </section>
        </div>

        <aside class="form-grid__side">
            <section class="panel stack">
                <h2>Publikasi</h2>
                <label class="switch">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published)) @disabled($page->is_system)>
                    <span>Terbit</span>
                </label>
                <label class="switch">
                    <input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer))>
                    <span>Tampil di footer</span>
                </label>
                <label class="field">
                    <span>Urutan di footer</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" min="0" max="999" inputmode="numeric">
                </label>
                @if (! $isNew && $page->is_published)
                    <a href="{{ route('slug.show', $page) }}" target="_blank" rel="noopener" class="link">Lihat halaman</a>
                @endif
            </section>

            <section class="panel stack">
                <h2>Tips</h2>
                <p class="hint">Pakai toolbar seperti di Google Docs. Judul, huruf tebal, daftar, tabel, dan tautan akan tampil persis seperti di halaman toko. Untuk menautkan halaman lain, tulis alamatnya saja, misalnya <code>/faq</code>.</p>
                <p class="hint">Tab <strong>Markdown</strong> di pojok kanan bawah editor menampilkan versi teks mentahnya.</p>
            </section>
        </aside>

        <div class="savebar only-mobile">
            <button type="submit" class="btn btn--primary btn--block">Simpan</button>
        </div>
    </form>

    @if (! $isNew && ! $page->is_system)
        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="danger-zone" onsubmit="return confirm('Hapus halaman ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger-text">@include('admin.partials.icon', ['name' => 'trash']) Hapus halaman</button>
        </form>
    @endif
@endsection

@push('scripts')
    <script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>
    <script>
        (function () {
            if (!window.toastui || !window.toastui.Editor) {
                return;
            }

            var source = document.querySelector('[data-editor-source]');
            var holder = document.querySelector('[data-editor]');
            var form = document.getElementById('page-form');

            holder.hidden = false;
            source.hidden = true;

            var editor = new toastui.Editor({
                el: holder,
                height: '560px',
                initialEditType: 'wysiwyg',
                previewStyle: 'tab',
                initialValue: source.value,
                usageStatistics: false,
                hideModeSwitch: false,
                toolbarItems: [
                    ['heading', 'bold', 'italic', 'strike'],
                    ['hr', 'quote'],
                    ['ul', 'ol', 'indent', 'outdent'],
                    ['table', 'link'],
                ],
            });

            form.addEventListener('submit', function () {
                source.value = editor.getMarkdown();
            });
        })();
    </script>
@endpush