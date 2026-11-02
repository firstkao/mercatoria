@if (setting('promo_bar_active', '1') !== '0')
    @php
        $promoText = trim((string) setting('promo_bar_text', ''));
        $promoLink = trim((string) setting('promo_bar_link', ''));
        $promoLink = preg_match('#^(https?://|/)#i', $promoLink) ? $promoLink : '';
    @endphp
    <div id="promo-bar" class="relative border-b border-[#D8D2C4] bg-[#F2EFE8] px-10 py-2 text-center">
        @if ($promoText !== '')
            @if ($promoLink !== '')
                <a href="{{ $promoLink }}" class="text-[13px] text-ink underline underline-offset-2">{!! clean_html($promoText) !!}</a>
            @else
                <div class="text-[13px] text-ink">{!! clean_html($promoText) !!}</div>
            @endif
        @endif
    </div>
@endif
