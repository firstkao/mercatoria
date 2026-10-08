@php($isNew = ! $page->exists)
@extends('admin.layouts.app', [
    'title' => $isNew ? 'Buat Halaman Pre-Order' : 'Edit: ' . $page->title,
    'back' => route('admin.preorder.index'),
])

@push('head')
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">
@endpush

@section('actions')
    <button type="submit" form="preorder-form" class="btn btn--primary only-desktop">Simpan</button>
@endsection

@section('content')
<form id="preorder-form" method="POST"
      action="{{ $isNew ? route('admin.preorder.store') : route('admin.preorder.update', $page) }}"
      enctype="multipart/form-data" class="form-grid" novalidate>
    @csrf
    @unless($isNew) @method('PUT') @endunless

    <div class="form-grid__main">
        <section class="panel stack">
            <label class="field">
                <span>Judul halaman</span>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" maxlength="200" required placeholder="Contoh: Pre-Order Baru — November 2026">
                @include('admin.partials.error', ['name' => 'title'])
            </label>

            <label class="field">
                <span>Slug (opsional)</span>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" maxlength="200" placeholder="otomatis dari judul">
                <small class="hint">Cuma internal. URL publiknya tetap <code>/pre-order-baru</code>.</small>
                @include('admin.partials.error', ['name' => 'slug'])
            </label>

            <div class="field">
                <span>Konten / tulisan</span>
                <textarea name="content" id="preorder-content" rows="20" class="mono-area" data-editor-source>{{ old('content', $page->content) }}</textarea>
                <div class="editor" data-editor hidden></div>
                @include('admin.partials.error', ['name' => 'content'])
            </div>
        </section>

        {{-- Banner existing --}}
        @if (! $isNew && $banners->isNotEmpty())
            <section class="panel stack">
                <h2>Banner Saat Ini ({{ $banners->count() }})</h2>
                <p class="hint">Atur link, alt text, urutan, dan aktif/nonaktif. Centang "Hapus" untuk membuang banner.</p>

                <div class="preorder-banner-list">
                    @foreach ($banners as $banner)
                        <div class="preorder-banner-item">
                            <img src="{{ $banner->imageUrl() }}" alt="" class="preorder-banner-item__thumb">

                            <div class="preorder-banner-item__fields">
                                <label class="field">
                                    <span>Link URL (opsional)</span>
                                    <input type="url" name="banners_existing[{{ $banner->id }}][link_url]"
                                           value="{{ old("banners_existing.{$banner->id}.link_url", $banner->link_url) }}"
                                           maxlength="500" placeholder="https://...">
                                </label>

                                <label class="field">
                                    <span>Alt text</span>
                                    <input type="text" name="banners_existing[{{ $banner->id }}][alt_text]"
                                           value="{{ old("banners_existing.{$banner->id}.alt_text", $banner->alt_text) }}"
                                           maxlength="255">
                                </label>

                                <div class="field-row">
                                    <label class="field field--short">
                                        <span>Urutan</span>
                                        <input type="number" name="banners_existing[{{ $banner->id }}][sort_order]"
                                               value="{{ old("banners_existing.{$banner->id}.sort_order", $banner->sort_order) }}"
                                               min="0" max="999">
                                    </label>

                                    <label class="switch mt-6">
                                        <input type="checkbox" name="banners_existing[{{ $banner->id }}][is_active]" value="1"
                                               @checked(old("banners_existing.{$banner->id}.is_active", $banner->is_active))>
                                        <span>Aktif</span>
                                    </label>
                                </div>

                                <label class="check check--danger">
                                    <input type="checkbox" name="remove_banners[]" value="{{ $banner->id }}">
                                    <span>Hapus banner ini</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Upload banner baru --}}
        <section class="panel stack">
            <h2>Tambah Banner Baru</h2>
            <p class="hint">Bisa pilih beberapa sekaligus. Ideal: rasio 16:9 atau 3:1, maks 4 MB per file. Akan otomatis dikonversi ke WebP.</p>

            <label class="upload">
                @include('admin.partials.icon', ['name' => 'image'])
                <span>Pilih file banner</span>
                <input type="file" name="banners[]" accept="image/jpeg,image/png,image/webp" multiple data-banner-input>
            </label>
            @include('admin.partials.error', ['name' => 'banners.*'])

            <div class="preorder-banner-preview" data-banner-preview></div>
        </section>
    </div>

    <aside class="form-grid__side">
        <section class="panel stack">
            <h2>Publikasi</h2>
            <label class="switch">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->is_active))>
                <span>Jadikan halaman aktif</span>
            </label>
            <p class="hint">Cuma satu halaman yang bisa aktif sekaligus. Kalau ini diaktifkan, halaman lain otomatis dinonaktifkan.</p>
            @include('admin.partials.error', ['name' => 'is_active'])
        </section>

        <section class="panel stack">
            <h2>Tips</h2>
            <p class="hint">
                Halaman ini cuma bisa dibuka pembeli yang sudah login. Cocok buat buka slot PO tertutup / limited.
                Tulis nama produk, deadline PO, harga estimasi, dan cara order di konten.
            </p>
        </section>
    </aside>

    <div class="savebar only-mobile">
        <button type="submit" class="btn btn--primary btn--block">Simpan</button>
    </div>
</form>

@unless($isNew)
    <form method="POST" action="{{ route('admin.preorder.destroy', $page) }}" class="danger-zone"
          onsubmit="return confirm('Hapus halaman ini beserta semua bannernya?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn--danger-text">Hapus halaman ini</button>
    </form>
@endunless
@endsection

@push('scripts')
<script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>
<script>
(function () {
    // Rich editor
    var source = document.querySelector('[data-editor-source]');
    var holder = document.querySelector('[data-editor]');
    var form = document.getElementById('preorder-form');

    if (window.toastui && window.toastui.Editor && holder && source) {
        holder.hidden = false;
        source.hidden = true;

        var editor = new toastui.Editor({
            el: holder,
            height: '500px',
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
    }

    // Preview banner baru
    var input = document.querySelector('[data-banner-input]');
    var preview = document.querySelector('[data-banner-preview]');
    if (input && preview) {
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.prototype.forEach.call(input.files, function (file) {
                var fig = document.createElement('figure');
                fig.style.cssText = 'margin:0;border:1px solid var(--border);border-radius:8px;overflow:hidden;background:var(--surface);';
                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.cssText = 'width:100%;height:120px;object-fit:cover;display:block;';
                var cap = document.createElement('figcaption');
                cap.textContent = file.name;
                cap.style.cssText = 'padding:6px 8px;font-size:11px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
                fig.appendChild(img);
                fig.appendChild(cap);
                preview.appendChild(fig);
            });
        });
    }
})();
</script>
@endpush