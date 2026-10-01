@extends('admin.layouts.app', ['title' => $product->name, 'back' => route('admin.products.index')])

@section('actions')
    <a href="{{ route('admin.products.edit', $product) }}" class="btn">Edit</a>
@endsection

@section('content')
<div class="panel">
    <div class="panel__head">
        <div>
            <h2 style="margin:0;">{{ $product->name }}</h2>
            <p class="muted" style="margin:4px 0 0;">
                <code>{{ $product->slug }}</code>
                @if ($product->sku) · SKU: <code>{{ $product->sku }}</code> @endif
            </p>
        </div>
        <div style="display:flex;gap:6px;">
            @if ($product->is_published)
                <span class="badge badge--on">Tayang</span>
            @else
                <span class="badge badge--muted">Draf</span>
            @endif

            @if ($product->tagLabel())
                <span class="tag-pill tag-pill--{{ $product->tag }}">{{ $product->tagLabel() }}</span>
            @endif

            @if ($product->isOnSale())
                <span class="badge badge--warn">Sedang diskon</span>
            @endif

            @if ($product->isOutOfStock())
                <span class="badge badge--danger">Stok habis</span>
            @endif
        </div>
    </div>

    <dl class="deflist">
        <div><dt>Game</dt><dd>{{ $product->game?->name ?? '—' }}</dd></div>
        <div><dt>Developer</dt><dd>{{ $product->developer?->name ?? '—' }}</dd></div>
        <div><dt>Tier ongkir</dt><dd>{{ $product->shippingTier?->code ?? '—' }}</dd></div>
        <div>
            <dt>Periode sale</dt>
            <dd>
                @if ($product->sale_starts_at && $product->sale_ends_at)
                    {{ $product->sale_starts_at->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}
                    –
                    {{ $product->sale_ends_at->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }} WIB
                @else
                    —
                @endif
            </dd>
        </div>
        <div><dt>Dibuat</dt><dd>{{ $product->created_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}</dd></div>
        <div><dt>Diperbarui</dt><dd>{{ $product->updated_at?->timezone('Asia/Jakarta')->translatedFormat('j M Y H:i') }}</dd></div>
    </dl>

    @if ($product->description)
        <div class="prose" style="margin-top:16px;">
            {!! nl2br(e($product->description)) !!}
        </div>
    @endif
</div>

{{-- ===== VARIAN ===== --}}
@if ($product->variants->isNotEmpty())
    <div class="panel panel--flush">
        <h3 style="padding:1rem 1rem 0;">Varian ({{ $product->variants->count() }})</h3>
        <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>SKU</th>
                    <th>Harga (¥)</th>
                    <th>Harga Coret (¥)</th>
                    <th>Berat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($product->variants as $variant)
                    <tr>
                        <td><strong>{{ $variant->name }}</strong></td>
                        <td><code>{{ $variant->sku ?: '—' }}</code></td>
                        <td>¥ {{ number_format((float) $variant->price_yuan, 2, '.', ',') }}</td>
                        <td class="muted">
                            @if ($variant->compare_price_yuan)
                                <del>¥ {{ number_format((float) $variant->compare_price_yuan, 2, '.', ',') }}</del>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $variant->weight_grams }} g</td>
                        <td>
                            @if ($variant->isAvailable())
                                <span class="badge badge--on">Tersedia</span>
                            @else
                                <span class="badge badge--muted">Habis</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endif

{{-- ===== GAMBAR ===== --}}
@if ($product->images->isNotEmpty())
    <div class="panel">
        <h3>Gambar ({{ $product->images->count() }})</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem;margin-top:.75rem;">
            @foreach ($product->images as $image)
                <figure style="margin:0;">
                    <img src="{{ $image->url() }}" alt="{{ $product->name }}"
                         style="width:100%;height:auto;border-radius:.5rem;">
                </figure>
            @endforeach
        </div>
    </div>
@endif
@endsection