# Panduan UI Area Admin

> **Aturan utama:** satu sumber kebenaran, satu layout, satu set komponen.
> Kalau sebuah halaman butuh tampilan berbeda, tambahkan **variant** ke komponen
> yang ada — **jangan** membuat komponen/class baru.

---

## 1. Sumber kebenaran (theme tokens)

**File:** `public/css/admin.css`, blok `:root` (bagian **0. DESIGN TOKENS**).

Semua warna, spacing, radius, tipografi, shadow, dan ukuran layout **wajib**
diambil dari token di sana. Status saat ini:

| Pemeriksaan | Hasil |
|---|---|
| Hex hardcoded di luar blok token | **0** |
| `font-size` mentah (px/rem) | **0** |
| `font-family` mentah | **0** |
| Spacing di luar kelipatan 4px | **0** |

### Token warna (semantik)

| Token | Dipakai untuk |
|---|---|
| `--bg`, `--surface`, `--surface-alt`, `--surface-sunken`, `--surface-hover` | Latar halaman & permukaan |
| `--text`, `--text-strong`, `--muted`, `--muted-strong`, `--on-accent` | Teks |
| `--border`, `--border-strong` | Garis |
| `--primary`, `--primary-hover`, `--primary-bg`, `--primary-border` | Brand |
| `--success*`, `--warning*`, `--danger*`, `--info-text` | State (masing-masing punya `-bg`, `-text`, `-border`) |
| `--sidebar*`, `--overlay`, `--surface-glass` | Shell & overlay |
| `--rank-1-*`, `--rank-2-*`, `--rank-3-*` | Medali peringkat |

### Token skala

| Kelompok | Token |
|---|---|
| Spacing (kelipatan 4px) | `--space-1` … `--space-12` (4 → 48px) |
| Radius | `--radius-sm` 4 · `--radius-md` 6 · `--radius` 8 · `--radius-lg` 12 · `--radius-pill` |
| Tipografi | `--font-sans`, `--font-mono`, `--fs-caption` … `--fs-display`, `--fw-*`, `--lh-*` |
| Shadow | `--shadow-xs`, `--shadow-sm`, `--shadow-md`, `--shadow-up` |
| Layout | `--sidebar-w`, `--topbar-h`, `--tabbar-h`, `--content-pad` |
| Z-index | `--z-topbar`, `--z-savebar`, `--z-tabbar`, `--z-sheet`, `--z-modal` |

> **Catatan `print.css`:** stylesheet cetak memuat salinan token palet yang sama
> (`--paper-*`) karena dimuat mandiri tanpa `admin.css`. Itu **satu-satunya**
> duplikasi yang diizinkan; nilainya harus selalu disamakan dengan `admin.css`.

---

## 2. Layout wrapper

**File:** `resources/views/admin/layouts/app.blade.php`
Struktur: `[Sidebar] + [Topbar] + [Content]`, plus `[Tabbar + Sheet]` untuk mobile.

```blade
@extends('admin.layouts.app', [
    'title' => 'Judul halaman',        // tampil di topbar
    'back'  => route('admin.x.index'), // opsional: tombol kembali
])

@section('actions')  {{-- tombol aksi di kanan topbar --}} @endsection
@section('tabs')     {{-- sub-tab, opsional --}}          @endsection
@section('filters')  {{-- baris filter, opsional --}}     @endsection

@section('content')
    {{-- isi halaman --}}
@endsection
```

**Judul halaman dirender otomatis oleh topbar. Jangan membuat judul halaman
kedua di dalam `content`.**

---

## 3. Library komponen

Semua di `resources/views/components/admin/`.

| Komponen | Kegunaan | Contoh |
|---|---|---|
| `<x-admin.button>` | Semua tombol | `<x-admin.button variant="primary" type="submit">Simpan</x-admin.button>` |
| `<x-admin.card>` | Container konten | `<x-admin.card padding="flush">…</x-admin.card>` |
| `<x-admin.badge>` | Status | `<x-admin.badge variant="on">Tayang</x-admin.badge>` |
| `<x-admin.alert>` | Notifikasi | `<x-admin.alert variant="danger">Gagal.</x-admin.alert>` |
| `<x-admin.page-header>` | Header + eyebrow/meta/actions | `<x-admin.page-header title="Laporan" eyebrow="Penjualan">…` |
| `<x-admin.empty-state>` | Data kosong | `<x-admin.empty-state title="Belum ada produk" hint="…" />` |
| `<x-admin.loading-state>` | Memuat | `<x-admin.loading-state label="Memuat…" />` |
| `<x-admin.table>` | Tabel | `<x-admin.table><x-slot:head>…</x-slot:head>…</x-admin.table>` |
| `<x-admin.modal>` | Dialog | `<x-admin.modal id="hapus" title="Hapus?">…</x-admin.modal>` |
| `<x-admin.input>` | Input teks | `<x-admin.input name="title" label="Judul" :value="$x" />` |
| `<x-admin.textarea>` | Textarea | `<x-admin.textarea name="body" label="Isi" rows="6" />` |
| `<x-admin.select>` | Select | `<x-admin.select name="status" :options="[…]" />` |
| `<x-admin.field>` | Bungkus kontrol manual | `<x-admin.field label="X" name="x"><input …></x-admin.field>` |
| `<x-admin.metric-strip>` | Baris angka besar | `<x-admin.metric-strip>…</x-admin.metric-strip>` |
| `<x-admin.metric>` | Satu angka besar | `<x-admin.metric :value="'12'" label="Produk" />` |

### Variant yang tersedia

- **button**: `default` · `primary` · `danger` · `danger-outline` · `danger-text` (+ `size="sm"`, `block`, `icon`, `href`)
- **badge**: `null` (netral) · `on` · `warn` · `danger` · `muted` (+ class `badge--info` bila perlu)
- **alert**: `success` · `warning` · `danger` · `info`
- **card**: `padding="flush"` (tabel) · `padding="inner"` (latar abu)

### Modal

```blade
<x-admin.button data-modal-open="hapus-produk" variant="danger">Hapus</x-admin.button>

<x-admin.modal id="hapus-produk" title="Hapus produk?">
    <p>Yakin ingin menghapus produk ini?</p>
    <x-slot:footer>
        <x-admin.button data-modal-close>Batal</x-admin.button>
        <x-admin.button variant="danger" type="submit" form="form-hapus">Hapus</x-admin.button>
    </x-slot:footer>
</x-admin.modal>
```

Handler JS: `public/js/admin-ui.js` (buka via `data-modal-open`, tutup via
`data-modal-close` / klik backdrop / tombol Esc).

---

## 4. Yang dilarang

- ❌ `style="…"` untuk warna/ukuran (inline style hanya untuk nilai dinamis,
  mis. lebar bar chart dari data).
- ❌ Hex/nama warna langsung di Blade atau CSS di luar blok token.
- ❌ Membuat tombol/card/table/badge versi sendiri (tulis HTML + class manual).
- ❌ Menyalin struktur sidebar/topbar ke halaman.
- ❌ File CSS per halaman.
- ❌ Judul halaman kedua di dalam `content` (sudah ada di topbar).
- ❌ Import library UI baru tanpa alasan kuat.

---

## 5. Aset & deployment

| File | Cara dimuat |
|---|---|
| `public/css/admin.css` | `<link>` di layout (cache-bust otomatis via `filemtime`) |
| `public/css/print.css` | hanya di halaman cetak |
| `public/js/admin-bulk.js` | `<script defer>` di layout |
| `public/js/admin-ui.js` | `<script defer>` di layout |

Aset admin ada di **`public/`** (bukan hasil build Vite), jadi **tidak perlu
`npm run build`** untuk mengubah tampilan admin. Cukup unggah file.
Setelah mengubah Blade, jalankan `php artisan view:clear`.

> Sumber `admin-bulk.js` ada di `resources/js/admin-bulk.js`; salinannya di
> `public/js/admin-bulk.js` yang benar-benar dimuat browser. Kalau mengubah
> sumbernya, **salin ulang** ke `public/js/`.

---

## 6. Status migrasi halaman

Lihat bagian **Status** di laporan pekerjaan. Ringkasnya:

- **Tahap 1 (selesai):** token lengkap, komponen shared, layout, login & cetak diselaraskan.
- **Tahap 2 (berjalan):** halaman dengan inline style terbanyak.
- **Tahap 3 (belum):** sisa halaman + penghapusan blok alias `dsh-*`/`rpt-*`.

Setelah **semua** halaman dimigrasi, blok **29. SHARED PRIMITIVES** bagian alias
(`.dsh-*`, `.rpt-*`, `.stat`, `.quick-action`) boleh dihapus agar hanya ada satu
bahasa komponen.
