<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Halo, {{ $order->user->full_name }}</h2>
    <p>Mohon maaf, bukti pembayaran untuk pesanan <strong>#{{ $order->order_number }}</strong> tidak dapat kami verifikasi dengan alasan:</p>
    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; color: #991b1b;">
        <strong>{{ $reason }}</strong>
    </div>
    <p>Silakan unggah ulang bukti transfer yang benar dalam waktu <strong>24 jam</strong> sejak email ini diterima agar pesanan Anda tidak dibatalkan otomatis oleh sistem.</p>
    <a href="{{ route('account.orders.show', $order) }}" style="display: inline-block; background: #0ea5e9; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Unggah Bukti Baru</a>
</div>
