@extends('layouts.admin')

@section('title', $page->exists ? 'Edit Preorder' : 'Buat Halaman Preorder')

@section('content')
    <div>
        <a href="{{ route('office.preorder-pages.index') }}" class="text-sm text-navy-500 hover:underline">← Halaman Preorder</a>
        <h1 class="mt-2 text-2xl font-bold text-navy-500">{{ $page->exists ? 'Edit Preorder' : 'Halaman Preorder Baru' }}</h1>
    </div>
    @if ($errors->any())
        <div role="alert" class="mt-4 rounded-lg bg-danger-50 p-4 text-sm text-danger-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <form method="POST" action="{{ $page->exists ? route('office.preorder-pages.update', $page) : route('office.preorder-pages.store') }}"
          class="mt-5 space-y-4 rounded-xl border border-border bg-white p-5">
        @csrf
        @if ($page->exists) @method('PUT') @endif
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium">Judul
                <input name="title" value="{{ old('title', $page->title) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Slug
                <input name="slug" value="{{ old('slug', $page->slug) }}" required maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
        </div>
        <section class="space-y-4 rounded-lg border border-border p-4" aria-labelledby="preorder-builder-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="preorder-builder-title" class="font-semibold">Builder halaman</h2>
                    <p class="text-xs text-text-light">Tambah blok, lalu seret atau gunakan tombol panah untuk mengatur urutannya.</p>
                </div>
                <div class="flex gap-2">
                    <label class="sr-only" for="new-block-type">Jenis blok</label>
                    <select id="new-block-type" class="rounded-lg border border-border px-3 py-2 text-sm">
                        <option value="heading">Heading</option>
                        <option value="text">Paragraf</option>
                        <option value="image">Gambar</option>
                        <option value="divider">Divider</option>
                        <option value="columns">Kolom</option>
                        <option value="spacer">Spacer</option>
                        <option value="html">HTML custom</option>
                    </select>
                    <button id="add-preorder-block" type="button" class="rounded-lg bg-navy-500 px-3 py-2 text-sm font-semibold text-white">Tambah blok</button>
                </div>
            </div>
            <div id="preorder-blocks" class="space-y-3"></div>
            <textarea id="preorder-grapes-json" name="grapes_json" required hidden>{{ old('grapes_json', $page->grapes_json ?: '[]') }}</textarea>
            <details>
                <summary class="cursor-pointer text-sm font-medium text-navy-500">Lihat atau sunting HTML hasil builder</summary>
                <label class="mt-3 block text-sm font-medium">HTML konten
                    <textarea id="preorder-html-content" name="html_content" rows="10" maxlength="100000"
                              class="mt-1 w-full rounded-lg border border-border px-3 py-2 font-mono text-sm">{{ old('html_content', $page->html_content) }}</textarea>
                    <span class="mt-1 block text-xs text-text-light">HTML disanitasi saat disimpan; script, SVG, atribut event, dan URL berbahaya akan dihapus.</span>
                </label>
            </details>
        </section>
        <div class="grid gap-4 md:grid-cols-2">
            <label class="text-sm font-medium">SEO title
                <input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255"
                       class="mt-1 w-full rounded-lg border border-border px-3 py-2">
            </label>
            <label class="text-sm font-medium">Status
                <select name="status" class="mt-1 w-full rounded-lg border border-border px-3 py-2">
                    <option value="draft" @selected(old('status', $page->status ?: 'draft') === 'draft')>Draft</option>
                    <option value="publish" @selected(old('status', $page->status) === 'publish')>Publish</option>
                </select>
            </label>
        </div>
        <label class="block text-sm font-medium">SEO description
            <textarea name="meta_description" rows="3" maxlength="500"
                      class="mt-1 w-full rounded-lg border border-border px-3 py-2">{{ old('meta_description', $page->meta_description) }}</textarea>
        </label>
        <div class="flex gap-3">
            <x-button type="submit">Simpan</x-button>
            <x-button variant="ghost" href="{{ route('office.preorder-pages.index') }}">Batal</x-button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('form[action*="preorder-pages"]');
            const blockList = document.getElementById('preorder-blocks');
            const typeSelect = document.getElementById('new-block-type');
            const jsonField = document.getElementById('preorder-grapes-json');
            const htmlField = document.getElementById('preorder-html-content');
            let blocks = [];
            let draggedIndex = null;

            try {
                const parsed = JSON.parse(jsonField.value);
                if (Array.isArray(parsed)) blocks = parsed;
            } catch {
                blocks = [];
            }
            if (blocks.length === 0 && htmlField.value.trim()) {
                blocks = [{ type: 'html', content: htmlField.value }];
            }
            blocks = blocks.map(block => ({
                type: ['heading', 'text', 'image', 'divider', 'columns', 'spacer', 'html'].includes(block.type)
                    ? block.type : 'text',
                content: String(block.content ?? ''),
            }));

            const escapeHtml = value => value.replace(/[&<>"']/g, character => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
            })[character]);

            const contentHtml = block => {
                const content = escapeHtml(block.content);
                switch (block.type) {
                    case 'heading': return `<h2>${content}</h2>`;
                    case 'text': return `<p>${content.replace(/\n/g, '<br>')}</p>`;
                    case 'image': return block.content ? `<img src="${content}" alt="">` : '';
                    case 'divider': return '<hr>';
                    case 'columns': {
                        const cells = block.content.split('|').slice(0, 4);
                        return `<table><tbody><tr>${cells.map(cell => `<td>${escapeHtml(cell.trim())}</td>`).join('')}</tr></tbody></table>`;
                    }
                    case 'spacer': {
                        const rows = Math.max(1, Math.min(10, Number.parseInt(block.content, 10) || 1));
                        return `<div>${'<br>'.repeat(rows)}</div>`;
                    }
                    case 'html': return block.content;
                    default: return '';
                }
            };

            const sync = () => {
                jsonField.value = JSON.stringify(blocks);
                htmlField.value = blocks.map(contentHtml).join('\n');
            };

            const render = () => {
                blockList.replaceChildren();
                blocks.forEach((block, index) => {
                    const card = document.createElement('div');
                    card.draggable = true;
                    card.className = 'rounded-lg border border-border bg-surface p-3';
                    card.addEventListener('dragstart', event => {
                        draggedIndex = index;
                        event.dataTransfer.setData('text/plain', String(index));
                    });
                    card.addEventListener('dragover', event => event.preventDefault());
                    card.addEventListener('drop', event => {
                        event.preventDefault();
                        const from = Number(event.dataTransfer.getData('text/plain'));
                        if (draggedIndex === null || from === index) return;
                        const [moved] = blocks.splice(from, 1);
                        blocks.splice(index, 0, moved);
                        draggedIndex = null;
                        sync();
                        render();
                    });

                    const header = document.createElement('div');
                    header.className = 'mb-2 flex items-center justify-between';
                    const title = document.createElement('strong');
                    title.textContent = `${index + 1}. ${block.type}`;
                    const actions = document.createElement('div');
                    actions.className = 'flex gap-2';
                    [['↑', -1, 'Pindahkan ke atas'], ['↓', 1, 'Pindahkan ke bawah']].forEach(([label, offset, description]) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = label;
                        button.setAttribute('aria-label', description);
                        button.disabled = index + offset < 0 || index + offset >= blocks.length;
                        button.addEventListener('click', () => {
                            [blocks[index], blocks[index + offset]] = [blocks[index + offset], blocks[index]];
                            sync();
                            render();
                        });
                        actions.append(button);
                    });
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.textContent = 'Hapus';
                    remove.className = 'text-danger-500';
                    remove.addEventListener('click', () => {
                        blocks.splice(index, 1);
                        sync();
                        render();
                    });
                    actions.append(remove);
                    header.append(title, actions);

                    const field = document.createElement(block.type === 'text' || block.type === 'columns' || block.type === 'html'
                        ? 'textarea' : 'input');
                    field.className = 'w-full rounded-lg border border-border px-3 py-2 text-sm';
                    field.value = block.content;
                    field.setAttribute('aria-label', `Konten ${block.type}`);
                    if (field.tagName === 'TEXTAREA') field.rows = block.type === 'html' ? 5 : 3;
                    if (block.type === 'image') field.placeholder = 'URL gambar (https://...)';
                    if (block.type === 'columns') field.placeholder = 'Isi kolom pisahkan dengan tanda |';
                    if (block.type === 'spacer') {
                        field.type = 'number';
                        field.min = '1';
                        field.max = '10';
                        field.placeholder = 'Jumlah baris jarak';
                    }
                    field.addEventListener('input', () => {
                        blocks[index].content = field.value;
                        sync();
                    });
                    card.append(header, field);
                    blockList.append(card);
                });
            };

            document.getElementById('add-preorder-block').addEventListener('click', () => {
                blocks.push({ type: typeSelect.value, content: typeSelect.value === 'spacer' ? '2' : '' });
                sync();
                render();
            });
            form.addEventListener('submit', sync);
            render();
        })();
    </script>
@endpush
