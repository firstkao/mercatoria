@php($remaining = $user->remainingViewQuota())
<div class="quota {{ $remaining <= 2 ? 'quota--warning' : '' }}" role="status">
    <strong>Sisa lihat produk: {{ $remaining }} dari {{ $viewQuota }}</strong>
    <p>Setiap membuka halaman produk mengurangi 1 kuota, termasuk produk yang sama. Kuota hilang setelah pembayaran pertamamu terverifikasi.</p>
</div>