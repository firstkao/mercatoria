<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentProofAdminNotification;
use App\Services\Admin\AdminNotificationService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
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
        $isPenalty = $order->status === OrderStatus::MenungguDenda->value;

        if (! $isPenalty && $order->status !== OrderStatus::MenungguPembayaran->value) {
            throw ValidationException::withMessages([
                'proof' => 'Upload bukti hanya untuk pesanan berstatus Menunggu Pembayaran atau Menunggu Denda.',
            ]);
        }

        $alreadyUploaded = $order->payments()
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_VERIFIED])
            ->exists();

        if (! $isPenalty && $alreadyUploaded) {
            throw ValidationException::withMessages([
                'proof' => 'Bukti bayar sudah pernah diunggah.',
            ]);
        }

        $paymentType = match (true) {
            $isPenalty => Payment::TYPE_PENALTY,
            $order->scheme === 'fp' => Payment::TYPE_FULL,
            default => Payment::TYPE_DP,
        };
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        // Nama file PRD 17.9: {no_order} - {tipe_pembayaran} - {metode_pembayaran}.ext
        $filename = sprintf('%s - %s - %s.%s', $order->no_order, $paymentType, $order->payment_method, $extension);

        // Disk 'local' = storage/app/private — tidak publik (PRD 17.7 / keamanan file).
        $path = $file->storeAs('payment-proofs', $filename, 'local');

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => $paymentType,
            'method' => $order->payment_method,
            'amount' => $isPenalty ? $order->penalty_amount : $order->tagihan_web,
            'file_path' => $path,
            'status' => Payment::STATUS_PENDING,
            'uploaded_at' => now(),
        ]);

        // PRD 17.11: email notif ke admin utama + cs@ saat bukti bayar masuk.
        Notification::send(
            User::admins()->get(),
            new PaymentProofAdminNotification($order, $payment)
        );
        Notification::route('mail', 'cs@mercatoria.id')
            ->notify(new PaymentProofAdminNotification($order, $payment));

        // PRD 41.1: bell admin — bukti bayar diupload, butuh verifikasi.
        app(AdminNotificationService::class)->notify(
            'bukti_bayar',
            'Bukti bayar #'.$order->no_order.' menunggu verifikasi',
            '/office/orders/'.$order->no_order
        );

        // Bukti denda (PRD 51.1) tidak mengunci slot Split Box — status tetap Menunggu Denda.
        if ($isPenalty) {
            return $payment;
        }

        // PRD 49.6–49.7: kunci slot Split Box SEBELUM status Ditahan (sinkron, atomik).
        // Gagal → order batal otomatis slot_sold → forfeit koin/voucher (pintu (e)).
        $locked = app(SplitBoxService::class)->lockSlotsForOrder($order);

        if (! $locked) {
            $order->update(['cancel_reason' => Order::CANCEL_SLOT_SOLD]);

            $statuses->changeStatus($order, OrderStatus::PembayaranGagal, 'system', 'slot_sold');

            return $payment;
        }

        $statuses->changeStatus($order, OrderStatus::Ditahan, 'user', 'Bukti bayar diunggah');

        return $payment;
    }
}
