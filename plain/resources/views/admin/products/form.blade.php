@php
    $isNew = ! $product->exists;
    $wib = fn ($date) => $date?->timezone('Asia/Jakarta')->format('Y-m-d\TH:i');
    $variantRows = old('variants', $product->variants->map(fn ($variant) => [
        'id' => $variant->id,
        'name' => $variant->name,
        'sku' => $variant->sku,
        'price_yuan' => $variant->price_yuan,
        'compare_price_yuan' => $variant->compare_price_yuan,
        'weight_grams' => $variant->weight_grams,
        'status' => $variant->status,
        'image_url' => $variant->imageUrl(),
    ])->all());
    if (empty($variantRows)) {
        $variantRows = [['name' => '', 'price_yuan' => '', 'compare_price_yuan' => '', 'weight_grams' => '', 'status' => 'available']];
    }
@endphp

@extends('admin.layouts.app', ['title' => $isNew ? 'Tambah produk' : 'Edit produk', 'back' => route('admin.products.index')])

@section('actions')
    <button type="submit" form="product-form" class="btn btn--primary only-desktop">Simpan</button>
@endsection

@section('content')
    <form id="product-form" method="POST" enctype="multipart/form-data" novalidate
          action="{{ $isNew ? route('admin.products.store') : route('admin.products.update', $product) }}"
          class="form-grid" data-product-form data-pricing='@json($pricing)'>
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <div class="form-grid__main">
            <section class="panel stack">
                <h2>Informasi produk</h2>

                <label class="field">
                    <span>Nama produk</span>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" maxlength="255" required data-name>
                    @include('admin.partials.error', ['name' => 'name'])
                </label>

                <label class="field">
                    <span>Alamat produk</span>
                    <div class="input-prefix">
                        <span>mercatoria.id/</span>
                        <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="255" placeholder="otomatis dari nama" data-slug>
                    </div>
                    @include('admin.partials.error', ['name' => 'slug'])
                </label>

                <label class="field">
                    <span>SKU</span>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="255" placeholder="mis. 周棋洛生日主题胸针">
                    <small class="hint">Nama barang di Taobao/Tmall. Tampil di halaman produk.</small>
                    @include('admin.partials.error', ['name' => 'sku'])
                </label>

                <div class="row-2">
                    <label class="field">
                        <span>Game</span>
                        <select name="game_id">
                            <option value="">—</option>
                            @foreach ($games as $game)
                                <option value="{{ $game->id }}" @selected((string) old('game_id', $product->game_id) === (string) $game->id)>{{ $game->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">
                        <span>Developer</span>
                        <select name="developer_id">
                            <option value="">—</option>
                            @foreach ($developers as $developer)
                                <option value="{{ $developer->id }}" @selected((string) old('developer_id', $product->developer_id) === (string) $developer->id)>{{ $developer->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="field">
                    <span>Deskripsi</span>
                    <textarea name="description" rows="5" maxlength="10000">{{ old('description', $product->description) }}</textarea>
                </label>
            </section>

            <section class="panel stack">
                <div class="panel__head">
                    <h2>Varian</h2>
                    <button type="button" class="btn btn--small" data-add-variant>@include('admin.partials.icon', ['name' => 'plus']) Tambah varian</button>
                </div>
                @include('admin.partials.error', ['name' => 'variants'])

                <div class="variants" data-variants data-next-index="{{ max(array_map('intval', array_keys($variantRows))) + 1 }}">
                    @foreach ($variantRows as $index => $row)
                        @include('admin.products.variant-row', ['index' => $index, 'row' => $row])
                    @endforeach
                </div>
                <p class="hint">Harga Rp dihitung otomatis dari harga yuan, berat, tier ongkir, dan pengaturan. Kalau simpan gagal, foto yang baru dipilih perlu dipilih ulang.</p>

                <template data-variant-template>
                    @include('admin.products.variant-row', ['index' => '__INDEX__', 'row' => ['name' => '', 'price_yuan' => '', 'compare_price_yuan' => '', 'weight_grams' => '', 'status' => 'available']])
                </template>
            </section>

        </div>

        <aside class="form-grid__side">
            <section class="panel stack">
                <h2>Publikasi</h2>
                <label class="switch">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $product->is_published))>
                    <span>Tayang di katalog</span>
                </label>
                <label class="field">
                    <span>Tag</span>
                    <select name="tag">
                        <option value="">Tanpa tag</option>
                        @foreach (\App\Models\Product::TAGS as $value => $label)
                            <option value="{{ $value }}" @selected(old('tag', $product->tag) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">
                    <span>Tier ongkir China</span>
                    <select name="shipping_tier_id" required data-tier>
                        <option value="">Pilih tier</option>
                        @foreach ($tiers as $tier)
                            <option value="{{ $tier->id }}" data-fee="{{ $tier->fee_yuan }}" data-min="{{ $tier->min_purchase_yuan }}"
                                    @selected((string) old('shipping_tier_id', $product->shipping_tier_id) === (string) $tier->id)>
                                {{ $tier->code }} · ¥{{ rtrim(rtrim($tier->fee_yuan, '0'), '.') }} / min ¥{{ rtrim(rtrim($tier->min_purchase_yuan, '0'), '.') }}
                            </option>
                        @endforeach
                    </select>
                    @include('admin.partials.error', ['name' => 'shipping_tier_id'])
                </label>
            </section>

            <section class="panel stack">
                <h2>Foto produk</h2>
                <div class="gallery" data-gallery>
                    @foreach ($product->images as $image)
                        <div @class(['gallery__item', 'is-main' => $loop->first])>
                            <label class="gallery__pick" title="Jadikan foto utama">
                                <input type="radio" name="main_image" value="{{ $image->id }}" @checked(old('main_image', $product->images->first()?->id) == $image->id)>
                                <img src="{{ $image->url() }}" alt="">
                                <span class="gallery__badge">Utama</span>
                            </label>
                            <label class="check check--small">
                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, old('remove_images', [])))>
                                <span>Hapus</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <label class="upload">
                    @include('admin.partials.icon', ['name' => 'image'])
                    <span>{{ $product->images->isEmpty() ? 'Pilih foto' : 'Tambah foto' }}</span>
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple data-image-input>
                </label>
                <small class="hint">Klik foto untuk menjadikannya foto utama. JPG, PNG, atau WebP, maks. 4 MB.</small>
                @include('admin.partials.error', ['name' => 'images.*'])
            </section>

            <section class="panel stack">
                <h2>Periode sale</h2>
                <p class="hint">Harga coret di varian hanya tampil selama periode ini (WIB).</p>
                <label class="field">
                    <span>Mulai</span>
                    <input type="datetime-local" name="sale_starts_at" value="{{ old('sale_starts_at', $wib($product->sale_starts_at)) }}">
                    @include('admin.partials.error', ['name' => 'sale_starts_at'])
                </label>
                <label class="field">
                    <span>Berakhir</span>
                    <input type="datetime-local" name="sale_ends_at" value="{{ old('sale_ends_at', $wib($product->sale_ends_at)) }}">
                    @include('admin.partials.error', ['name' => 'sale_ends_at'])
                </label>
            </section>

            @unless ($isNew)
                <p class="side-links">
                    @if ($product->is_published)
                        <a href="{{ route('slug.show', $product) }}" target="_blank" rel="noopener" class="link">Lihat di toko</a>
                    @endif
                </p>
            @endunless
        </aside>

        <div class="savebar only-mobile">
            <button type="submit" class="btn btn--primary btn--block">Simpan</button>
        </div>
    </form>

    @unless ($isNew)
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="danger-zone"
              onsubmit="return confirm('Hapus produk ini? Pesanan lama tetap tersimpan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger-text">@include('admin.partials.icon', ['name' => 'trash']) Hapus produk</button>
        </form>
    @endunless
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.querySelector('[data-product-form]');
            var pricing = JSON.parse(form.dataset.pricing);
            var list = form.querySelector('[data-variants]');
            var template = form.querySelector('[data-variant-template]');
            var tier = form.querySelector('[data-tier]');
            var nameInput = form.querySelector('[data-name]');
            var slugInput = form.querySelector('[data-slug]');
            var nextIndex = parseInt(list.dataset.nextIndex, 10);

            // Mirrors App\Support\PriceCalculator so the admin sees the price before saving.
            function sellingPrice(yuan, grams) {
                var option = tier.selectedOptions[0];
                if (!pricing.exchangeRate || pricing.cnIdRatePerKg === null || pricing.btmJktRatePerKg === null || !option || !option.value || !yuan || !grams) {
                    return null;
                }
                var fee = parseFloat(option.dataset.fee), minimum = parseFloat(option.dataset.min);
                var china = (fee > 0 && minimum > 0 && yuan < minimum) ? yuan * fee / minimum : 0;
                var net = (yuan + china) * pricing.exchangeRate + (grams / 1000) * (pricing.cnIdRatePerKg + pricing.btmJktRatePerKg);
                var price = net * (1 + pricing.marginPercent / 100);
                return Math.ceil(Math.round(price / pricing.roundingStep * 1e6) / 1e6) * pricing.roundingStep;
            }

            function rupiah(value) {
                return 'Rp' + value.toLocaleString('id-ID');
            }

            function refresh(row) {
                var grams = parseFloat(row.querySelector('[data-weight]').value);
                var price = sellingPrice(parseFloat(row.querySelector('[data-price]').value), grams);
                var compare = sellingPrice(parseFloat(row.querySelector('[data-compare]').value), grams);
                var output = row.querySelector('[data-preview]');
                if (price === null) {
                    output.textContent = pricing.exchangeRate ? 'Isi harga, berat, dan tier' : 'Isi kurs di Pengaturan';
                    return;
                }
                output.textContent = rupiah(price) + (compare && compare > price ? ' (coret ' + rupiah(compare) + ')' : '');
            }

            function refreshAll() {
                list.querySelectorAll('[data-variant]').forEach(refresh);
            }

            list.addEventListener('input', function (event) {
                var row = event.target.closest('[data-variant]');
                if (row) {
                    refresh(row);
                }
            });

            list.addEventListener('click', function (event) {
                var button = event.target.closest('[data-remove-variant]');
                if (!button) {
                    return;
                }
                if (list.children.length === 1) {
                    alert('Produk harus punya minimal satu varian.');
                    return;
                }
                if (confirm('Hapus varian ini? Perubahan berlaku setelah Simpan.')) {
                    button.closest('[data-variant]').remove();
                }
            });

            form.querySelector('[data-add-variant]').addEventListener('click', function () {
                var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex++));
                list.insertAdjacentHTML('beforeend', html);
                var row = list.lastElementChild;
                row.querySelector('input').focus();
                refresh(row);
            });

            tier.addEventListener('change', refreshAll);

            var slugEdited = slugInput.value !== '';
            slugInput.addEventListener('input', function () { slugEdited = slugInput.value !== ''; });
            nameInput.addEventListener('input', function () {
                if (!slugEdited) {
                    slugInput.placeholder = nameInput.value.toLowerCase().normalize('NFKD').replace(/[^\w\s-]/g, '').trim().replace(/[\s_-]+/g, '-') || 'otomatis dari nama';
                }
            });

            var gallery = form.querySelector('[data-gallery]');
            var imageInput = form.querySelector('[data-image-input]');
            imageInput.addEventListener('change', function () {
                gallery.querySelectorAll('[data-new-image]').forEach(function (item) { item.remove(); });
                Array.prototype.forEach.call(imageInput.files, function (file, index) {
                    var item = document.createElement('div');
                    item.className = 'gallery__item';
                    item.dataset.newImage = '';
                    item.innerHTML = '<label class="gallery__pick" title="Jadikan foto utama"><input type="radio" name="main_image" value="new-' + index + '"><img alt=""><span class="gallery__badge">Utama</span></label><span class="hint">Baru</span>';
                    item.querySelector('img').src = URL.createObjectURL(file);
                    gallery.appendChild(item);
                });
                if (!gallery.querySelector('input[name=main_image]:checked')) {
                    var first = gallery.querySelector('input[name=main_image]');
                    if (first) { first.checked = true; }
                }
                markMain();
            });

            function markMain() {
                gallery.querySelectorAll('.gallery__item').forEach(function (item) {
                    var radio = item.querySelector('input[name=main_image]');
                    item.classList.toggle('is-main', !!(radio && radio.checked));
                });
            }
            gallery.addEventListener('change', markMain);
            markMain();

            refreshAll();
        })();
    </script>
@endpush