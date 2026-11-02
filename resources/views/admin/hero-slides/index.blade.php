@extends('admin.layouts.admin')

@section('title', 'Hero Slides')

@section('content')
    <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-ink/45">Konten</p>
    <h1 class="mt-2 font-display text-[1.5rem] font-semibold text-ink">Hero Slides</h1>

    <form method="POST" action="{{ route('office.hero-slides.store') }}" enctype="multipart/form-data" class="mt-4 rounded-xl border border-rule bg-white p-4">
        @csrf
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Upload Gambar</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required
                       class="mt-1 block w-56 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                <img src="" alt="" id="create-preview" class="mt-2 h-20 rounded border border-rule object-cover" style="display:none">
            </div>
            <div>
                <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Urutan</label>
                <input type="number" name="sort_order" min="0" value="0"
                       class="mt-1 w-24 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
            </div>
            <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-rule"> Aktif
            </label>
            <button class="rounded-lg bg-black px-4 py-2 text-sm font-semibold tracking-[0.12em] text-white transition-colors hover:bg-ink">+ Tambah</button>
        </div>
    </form>

    <div class="mt-4 space-y-3">
        @forelse ($slides as $slide)
            <div class="rounded-xl border border-rule bg-white p-4">
                <form method="POST" action="{{ route('office.hero-slides.update', $slide) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Gambar Saat Ini</label>
                        @if ($slide->image)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($slide->image) }}" alt="Slide {{ $slide->id }}"
                                 class="mt-1 h-20 rounded border border-rule object-cover">
                        @endif
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Ganti Gambar</label>
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"
                               class="mt-1 block w-48 rounded-lg border border-rule px-2 py-1.5 text-xs text-ink">
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] font-medium uppercase tracking-[0.14em] text-ink/70">Urutan</label>
                        <input type="number" name="sort_order" min="0" value="{{ $slide->sort_order }}"
                               class="mt-1 w-24 rounded-lg border border-rule px-3 py-2 text-sm text-ink">
                    </div>
                    <label class="flex items-center gap-1 py-2 text-sm text-ink/80">
                        <input type="checkbox" name="is_active" value="1" @checked($slide->is_active) class="rounded border-rule"> Aktif
                    </label>
                    <button class="rounded-lg border border-rule px-4 py-2 text-sm font-semibold text-ink transition-colors hover:border-ink">Simpan</button>
                </form>
                <div class="mt-2 flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-ink/45">#{{ $slide->id }} · {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    <form method="POST" action="{{ route('office.hero-slides.destroy', $slide) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="rounded-lg border border-danger/40 px-3 py-1 text-xs font-semibold text-danger transition-colors hover:border-danger"
                                data-confirm="Hapus hero slide ini?" data-confirm-title="Hapus Hero Slide" data-confirm-text="Hapus" data-confirm-danger="1">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-rule bg-white p-8 text-center text-sm text-ink/45">
                Belum ada hero slide.
            </div>
        @endforelse
    </div>

    <script>
        (function () {
            var input = document.querySelector('form input[name="image"]');
            var preview = document.getElementById('create-preview');
            if (!input || !preview) return;
            input.addEventListener('change', function () {
                if (this.files && this.files.length) {
                    preview.src = URL.createObjectURL(this.files[0]);
                    preview.style.display = '';
                } else {
                    preview.removeAttribute('src');
                    preview.style.display = 'none';
                }
            });
        })();
    </script>
@endsection
