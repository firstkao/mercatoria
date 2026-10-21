@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class='mx-auto max-w-7xl px-4 py-8'>
    <h1 class='text-2xl font-bold text-navy-500'>Keranjang Belanja ({{ $lines->count() }} Item)</h1>
    @auth
        @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile())<x-profile-banner />@endif
    @endauth
    @if (session('success'))<p role='status' class='mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800'>{{ session('success') }}</p>@endif
    @if ($errors->any())<div role='alert' class='mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800'>{{ $errors->first() }}</div>@endif

    @if ($lines->isEmpty())
        <div class='mt-6 rounded-xl border border-dashed border-border bg-white p-8 text-center'>
            <p>Keranjang masih kosong.</p>
            <a href='{{ route('catalog.index') }}' class='mt-3 inline-block font-semibold text-navy-500 hover:underline'>Jelajahi katalog</a>
        </div>
    @else
        <form id='cart-checkout-selection' method='GET' action='{{ route('cart.checkout') }}'></form>
        <form id='cart-remove-selected' method='POST' action='{{ route('cart.remove-selected') }}'>@csrf @method('DELETE')</form>
        <div class='mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]'>
            <div class='space-y-4'>
                <div class='flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border bg-white p-4'>
                    <label class='inline-flex items-center gap-2 font-medium'><input id='cart-select-all' type='checkbox' class='rounded border-border text-primary-600'> Pilih Semua ({{ $lines->count() }})</label>
                    <button type='button' data-remove-selected class='rounded-lg px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50'>Hapus Terpilih</button>
                </div>
                @foreach ($lines as $line)
                    @php($cart = $line['cart'])
                    <article class='grid grid-cols-[auto_64px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-border bg-white p-4 sm:grid-cols-[auto_64px_minmax(0,1fr)_auto] sm:items-center'>
                        <input form='cart-checkout-selection' type='checkbox' name='cart_ids[]' value='{{ $cart->id }}' aria-label='Pilih {{ $cart->product->name }}' class='cart-item-check rounded border-border text-primary-600'>
                        @php($cartImage = $cart->variant?->image_path ?? $cart->product->main_image_path)
                        @if ($cartImage)<div class='protected-image-wrap h-16 w-16 rounded-lg'><img src='{{ \Illuminate\Support\Facades\Storage::url($cartImage) }}' alt='' class='protected-image h-16 w-16 rounded-lg object-cover' draggable='false'><span class='img-shield-overlay' aria-hidden='true'></span></div>@else<div class='h-16 w-16 rounded-lg bg-surface-2'></div>@endif
                        <div class='min-w-0'>
                            <p class='text-xs font-semibold uppercase text-text-muted'>{{ $cart->product->games->first()?->name ?? 'Mercatoria' }}</p>
                            <h2 class='truncate font-semibold'>{{ $cart->product->name }}</h2>
                            @if ($cart->variant)<p class='text-sm text-text-muted'>Varian: {{ $cart->variant->name }}</p>@endif
                            <p class='mt-1 text-sm font-semibold text-navy-500'>{{ format_rupiah($line['pricing']['final_price']) }} / item</p>
                        </div>
                        <div class='col-span-3 flex flex-wrap items-center justify-between gap-3 sm:col-span-1 sm:justify-end'>
                            <form method='POST' action='{{ route('cart.update', $cart) }}' class='flex items-center gap-2'>
                                @csrf @method('PATCH')
                                <label class='sr-only' for='qty-{{ $cart->id }}'>Jumlah {{ $cart->product->name }}</label>
                                <input id='qty-{{ $cart->id }}' name='qty' type='number' min='1' max='99' value='{{ $cart->qty }}' data-cart-qty class='w-20 rounded-lg border-border text-sm'>
                                <span class='font-semibold' data-cart-total data-unit-price='{{ $line['pricing']['final_price'] }}' data-base-unit-price='{{ $line['pricing']['base_price'] }}'>{{ format_rupiah($line['pricing']['final_price'] * $cart->qty) }}</span>
                                <button type='submit' class='rounded-lg border border-border px-2 py-1 text-xs'>Simpan</button>
                            </form>
                            <form method='POST' action='{{ route('cart.remove', $cart) }}'>@csrf @method('DELETE')<button type='submit' aria-label='Hapus {{ $cart->product->name }}' class='rounded-lg px-3 py-2 text-red-700 hover:bg-red-50'>🗑️</button></form>
                        </div>
                    </article>
                @endforeach

                <form method='POST' action='{{ route('cart.note') }}' class='rounded-xl border border-border bg-white p-4'>
                    @csrf
                    <label for='customer_note' class='font-semibold'>📝 Catatan untuk Mercatoria (Opsional)</label>
                    <textarea id='customer_note' name='customer_note' rows='3' maxlength='500' placeholder='Contoh: bubble wrap extra, packing rapi, mau bonus A/B' class='mt-2 w-full rounded-lg border-border text-sm'>{{ old('customer_note', $customerNote) }}</textarea>
                    <div class='mt-2 flex items-center justify-between gap-3'><span class='text-xs text-text-muted'><span id='customer-note-count'>{{ mb_strlen((string) old('customer_note', $customerNote)) }}</span> / 500 karakter. Catatan ikut tersimpan di pesanan.</span><button type='submit' class='rounded-lg border border-border px-3 py-2 text-sm font-semibold hover:bg-surface-2'>Simpan Catatan</button></div>
                </form>
            </div>

            <aside class='rounded-xl border border-border bg-white p-5 lg:sticky lg:top-24'>
                <h2 class='text-lg font-bold'>Ringkasan Belanja</h2>
                <div class='mt-4 flex justify-between text-sm'><span>Item Terpilih</span><strong><span id='selected-count'>0</span> item</strong></div>
                <div class='mt-2 flex justify-between text-sm'><span>Subtotal</span><strong id='selected-subtotal'>Rp 0</strong></div>
                <div class='mt-2 flex justify-between text-sm'><span>Diskon</span><strong id='selected-discount'>Rp 0</strong></div>
                <div class='mt-3 flex justify-between border-t border-border pt-3 text-base font-bold'><span>TOTAL</span><strong id='selected-total'>Rp 0</strong></div>
                <p class='mt-3 rounded-lg bg-surface-2 p-3 text-xs text-text-muted'>ℹ️ Harga sudah bersih, termasuk ongkir CN-ID. Tidak ada rincian ongkir atau berat pada keranjang.</p>
                <button form='cart-checkout-selection' type='submit' data-checkout-selected disabled @if (auth()->user()->hasVerifiedEmail() && ! auth()->user()->hasCompletedProfile()) data-profile-incomplete @endif class='mt-4 w-full rounded-lg bg-[#4FC3F7] px-5 py-3 font-semibold text-white hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-50'>Lanjut ke Checkout</button>
            </aside>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const cartChecks = [...document.querySelectorAll('.cart-item-check')];
    const selectAllCart = document.getElementById('cart-select-all');
    const checkoutSelected = document.querySelector('[data-checkout-selected]');
    const selectedCount = document.getElementById('selected-count');
    const selectedSubtotal = document.getElementById('selected-subtotal');
    const selectedDiscount = document.getElementById('selected-discount');
    const selectedTotal = document.getElementById('selected-total');
    const removeSelectedForm = document.getElementById('cart-remove-selected');
    const customerNoteInput = document.getElementById('customer_note');
    const customerNoteCount = document.getElementById('customer-note-count');
    const updateCustomerNoteCount = () => {
        if (customerNoteCount) customerNoteCount.textContent = customerNoteInput.value.length;
    };
    customerNoteInput?.addEventListener('input', updateCustomerNoteCount);
    updateCustomerNoteCount();
    const formatCartPrice = amount => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount);
    function updateCartSelection() {
        const selected = cartChecks.filter(input => input.checked);
        const totals = selected.reduce((sum, input) => {
            const article = input.closest('article');
            const quantity = Number(article.querySelector('[data-cart-qty]').value || 1);
            const price = article.querySelector('[data-cart-total]').dataset;
            const finalPrice = Number(price.unitPrice);
            const basePrice = Number(price.baseUnitPrice);
            sum.subtotal += basePrice * quantity;
            sum.discount += Math.max(0, basePrice - finalPrice) * quantity;
            sum.total += finalPrice * quantity;
            return sum;
        }, { subtotal: 0, discount: 0, total: 0 });
        if (selectedCount) selectedCount.textContent = selected.length;
        if (selectedSubtotal) selectedSubtotal.textContent = formatCartPrice(totals.subtotal);
        if (selectedDiscount) selectedDiscount.textContent = formatCartPrice(totals.discount);
        if (selectedTotal) selectedTotal.textContent = formatCartPrice(totals.total);
        if (checkoutSelected) checkoutSelected.disabled = selected.length === 0 || checkoutSelected.hasAttribute('data-profile-incomplete');
        if (selectAllCart) selectAllCart.checked = selected.length === cartChecks.length;
    }
    cartChecks.forEach(input => input.addEventListener('change', updateCartSelection));
    selectAllCart?.addEventListener('change', () => { cartChecks.forEach(input => input.checked = selectAllCart.checked); updateCartSelection(); });
    document.querySelectorAll('[data-cart-qty]').forEach(input => input.addEventListener('input', () => {
        const article = input.closest('article');
        const price = Number(article.querySelector('[data-cart-total]').dataset.unitPrice);
        article.querySelector('[data-cart-total]').textContent = formatCartPrice(price * Number(input.value || 1));
        updateCartSelection();
    }));
    document.querySelector('[data-remove-selected]')?.addEventListener('click', event => {
        event.preventDefault();
        removeSelectedForm.querySelectorAll('input[name="cart_ids[]"]').forEach(input => input.remove());
        cartChecks.filter(input => input.checked).forEach(input => {
            const hidden = document.createElement('input'); hidden.type = 'hidden'; hidden.name = 'cart_ids[]'; hidden.value = input.value; removeSelectedForm.append(hidden);
        });
        if (cartChecks.some(input => input.checked)) removeSelectedForm.requestSubmit();
    });
    updateCartSelection();

    if (typeof window.mercatoriaTrack === 'function') {
        window.mercatoriaTrack('view_cart', {
            currency: 'IDR',
            value: @js((float) $lines->sum(fn ($line) => $line['pricing']['final_price'] * $line['cart']->qty)),
            items: @js($lines->map(fn ($line) => [
                'item_id' => $line['cart']->product->sku,
                'item_name' => $line['cart']->product->name,
                'item_variant' => $line['cart']->variant?->name,
                'price' => (float) $line['pricing']['final_price'],
                'quantity' => $line['cart']->qty,
            ])->values())
        });
    }
</script>
@endpush
