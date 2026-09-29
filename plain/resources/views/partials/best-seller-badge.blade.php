@if ($product->isBestSeller())
    <span class="tag tag--best-seller" title="Produk terlaris #{{ $product->best_seller_rank }}">
        🏆 Best Seller
    </span>
@endif