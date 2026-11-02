@if (setting('promo_bar_active', '1') !== '0')
    @php
        $promoText = trim((string) setting('promo_bar_text', ''));
        $promoLink = trim((string) setting('promo_bar_link', ''));
        $promoLink = preg_match('#^(https?://|/)#i', $promoLink) ? $promoLink : '';
    @endphp
    <div id="promo-bar" class="relative bg-[#F2EFE8] px-10 py-2 text-center border-b border-[#D8D2C4]">
        <p class="text-[13px] text-ink">
            @if ($promoText !== '')
                @if ($promoLink !== '')
                    <a href="{{ $promoLink }}" class="underline underline-offset-2">{{ $promoText }}</a>
                @else
                    {{ $promoText }}
                @endif
            @else
                Gunakan kode promo <strong class="font-semibold text-danger">NEWWEB</strong> dan nikmati potongan harga sebesar <strong class="font-semibold italic">Rp {{ number_format((float) setting('promo_bar_discount', 25000)) }}</strong> dengan minimal pembelian <strong class="font-semibold italic">Rp {{ number_format((float) setting('promo_bar_min_purchase', 150000)) }}</strong>.
            @endif
        </p>
    </div>
@endif
