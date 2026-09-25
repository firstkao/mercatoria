<div class="variant" data-variant>
    @if (! empty($row['id']))
        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $row['id'] }}">
    @endif

    <div class="variant__grid">
        <label class="field variant__name">
            <span>Nama varian</span>
            <input type="text" name="variants[{{ $index }}][name]" value="{{ $row['name'] }}" maxlength="255" placeholder="mis. Luke" required>
            @include('admin.partials.error', ['name' => "variants.{$index}.name"])
        </label>
        <label class="field variant__sku">
            <span>SKU varian</span>
            <input type="text" name="variants[{{ $index }}][sku]" value="{{ $row['sku'] ?? '' }}" maxlength="255" placeholder="opsional">
        </label>
        <label class="field">
            <span>Harga (¥)</span>
            <input type="number" name="variants[{{ $index }}][price_yuan]" value="{{ $row['price_yuan'] }}" step="0.01" min="0" inputmode="decimal" required data-price>
            @include('admin.partials.error', ['name' => "variants.{$index}.price_yuan"])
        </label>
        <label class="field">
            <span>Harga coret (¥)</span>
            <input type="number" name="variants[{{ $index }}][compare_price_yuan]" value="{{ $row['compare_price_yuan'] ?? '' }}" step="0.01" min="0" inputmode="decimal" placeholder="opsional" data-compare>
            @include('admin.partials.error', ['name' => "variants.{$index}.compare_price_yuan"])
        </label>
        <label class="field">
            <span>Berat (gram)</span>
            <input type="number" name="variants[{{ $index }}][weight_grams]" value="{{ $row['weight_grams'] }}" step="1" min="1" inputmode="numeric" required data-weight>
            @include('admin.partials.error', ['name' => "variants.{$index}.weight_grams"])
        </label>
        <label class="field">
            <span>Status</span>
            <select name="variants[{{ $index }}][status]">
                <option value="available" @selected(($row['status'] ?? 'available') === 'available')>Tersedia</option>
                <option value="out_of_stock" @selected(($row['status'] ?? '') === 'out_of_stock')>Out of stock</option>
            </select>
        </label>
    </div>

    <div class="variant__footer">
        <span class="variant__preview">Harga jual: <strong data-preview>—</strong></span>
        <span class="variant__tools">
            @if (! empty($row['image_url']))
                <img src="{{ $row['image_url'] }}" alt="" class="variant__thumb">
                <label class="check check--small">
                    <input type="checkbox" name="variants[{{ $index }}][remove_image]" value="1">
                    <span>Hapus foto</span>
                </label>
            @endif
            <label class="file-link">
                @include('admin.partials.icon', ['name' => 'image'])
                <span>{{ ! empty($row['image_url']) ? 'Ganti foto' : 'Foto varian' }}</span>
                <input type="file" name="variants[{{ $index }}][image]" accept="image/jpeg,image/png,image/webp">
            </label>
            <button type="button" class="icon-btn" data-remove-variant aria-label="Hapus varian">@include('admin.partials.icon', ['name' => 'trash'])</button>
        </span>
    </div>
    @include('admin.partials.error', ['name' => "variants.{$index}.image"])
</div>