<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentProofService
{
    /**
     * Simpan bukti bayar (PRD 17.9–17.10) di storage privat + kunci form 1x.
     *
     * @throws ValidationException
     */
    public function upload(Order $order, UploadedFile $file, User $user, OrderStatusService $statuses): Payment
    {
        if ($order->status !== OrderStatus::MenungguPembayaran->value) {
            throw ValidationException::withMessages([
                'proof' => 'Upload bukti hanya untuk pesanan berstatus Menunggu Pembayaran.',
            ]);
        }

        $alreadyUploaded = $order->payments()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_VERIFIED])
            ->exists();

        if ($alreadyUploaded) {
            throw ValidationException::withMessages([
                'proof' => 'Bukti bayar sudah pernah diunggah.',
            ]);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        // Nama file PRD 17.9: {no_order} - {tipe_pembayaran} - {metode_pembayaran}.ext
        $filename = sprintf('%s - %s - %s.%s', $order->no_order, $order->scheme, $order->payment_method, $extension);

        // Disk 'local' = storage/app/private — tidak publik (PRD 17.7 / keamanan file).
        $path = $file->storeAs('payment-proofs', $filename, 'local');

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => $order->scheme,
            'method' => $order->payment_method,
            'amount' => $order->tagihan_web,
            'file_path' => $path,
            'status' => Payment::STATUS_PENDING,
            'uploaded_at' => now(),
        ]);

        $statuses->changeStatus($order, OrderStatus::Ditahan, 'user', 'Bukti bayar diunggah');

        return $payment;
    }
}
