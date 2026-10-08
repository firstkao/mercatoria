@php
    $waNumber = $order->user?->whatsappLink();
    $placeholders = [
        '{nama}' => (string) ($order->user?->full_name ?? 'Kak'),
        '{order_number}' => (string) $order->order_number,
        '{total}' => \App\Support\PriceCalculator::formatRupiah($order->total_idr),
        '{dibayar}' => \App\Support\PriceCalculator::formatRupiah($order->pay_now_idr),
        '{status}' => $order->statusLabel(),
        '{deadline}' => $order->payment_deadline_at
            ? $order->payment_deadline_at->timezone('Asia/Jakarta')->translatedFormat('j F Y, H:i') . ' WIB'
            : '—',
        '{tanggal}' => $order->created_at->timezone('Asia/Jakarta')->translatedFormat('j F Y'),
    ];

    $templates = [
        'Bayar' => \App\Models\Setting::get('wa_template_payment_reminder', 'Halo {nama}, pesanan #{order_number} masih menunggu pembayaran. Batas waktu {deadline}. Yuk selesaikan ya 🙏'),
        'Diterima' => \App\Models\Setting::get('wa_template_payment_received', 'Halo {nama}, pembayaran pesanan #{order_number} sudah kami terima. Terima kasih! Pesanan segera diproses.'),
        'Diproses' => \App\Models\Setting::get('wa_template_processing', 'Halo {nama}, pesanan #{order_number} sedang kami proses. Update selanjutnya akan kami infokan ya.'),
        'Dikirim' => \App\Models\Setting::get('wa_template_shipped', 'Halo {nama}, pesanan #{order_number} sudah dalam perjalanan ke Indonesia. Estimasi tiba ~45 hari setelah keluar dari gudang China.'),
        'Selesai' => \App\Models\Setting::get('wa_template_completed', 'Halo {nama}, pesanan #{order_number} sudah selesai. Terima kasih sudah berbelanja di MERCATORIA! 🎉'),
    ];
@endphp

@if ($waNumber)
    <details class="wa-menu">
        <summary class="wa-menu__trigger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="wa-icon">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            Kirim WA
        </summary>

        <div class="wa-menu__list">
            @foreach ($templates as $label => $template)
                @php $text = strtr($template, $placeholders); @endphp
                <a href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode($text) }}"
                   target="_blank" rel="noopener"
                   class="wa-menu__item">
                    <strong>{{ $label }}</strong>
                    <span>{{ \Illuminate\Support\Str::limit($text, 60) }}</span>
                </a>
            @endforeach

            <a href="https://wa.me/{{ $waNumber }}"
               target="_blank" rel="noopener"
               class="wa-menu__item wa-menu__item--blank">
                <strong>Custom</strong>
                <span>Chat kosong — tulis sendiri</span>
            </a>
        </div>
    </details>
@else
    <span class="muted small">WA tidak tersedia (nomor kosong)</span>
@endif