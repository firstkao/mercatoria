<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto;">
    <h2>Update Pesanan #{{ $order->order_number }}</h2>
    <p>Halo, {{ $order->user->full_name }},</p>
    <p>Status pesanan Anda telah diperbarui menjadi:</p>
    <h3 style="color: #0ea5e9;">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($order->status)) }}</h3>
    
    @if($order->status === 'selesai')
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; color: #065f46;">
            <strong>Pesanan Selesai!</strong> Koin cashback sebesar {{ number_format($order->coin_estimate, 0, ',', '.') }} telah dimasukkan ke akun Anda.
        </div>
    @endif

    <p>Anda dapat melihat rincian lengkap pesanan melalui tombol di bawah ini:</p>
    <a href="{{ route('account.orders.show', $order) }}" style="display: inline-block; background: #0ea5e9; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Cek Pesanan Saya</a>
</div>
