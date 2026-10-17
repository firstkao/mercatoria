<?php

namespace App\Services\Order;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Enums\UserState;
use App\Models\AdminNotification;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Services\Notification\UserNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OrderPaymentService
{
    public function uploadProof(Order $order, UploadedFile $proof): Payment
    {
        if (! in_array($order->status, [
            OrderStatus::MENUNGGU_PEMBAYARAN->value,
            OrderStatus::MENUNGGU_PELUNASAN->value,
        ], true)) {
            throw ValidationException::withMessages(['proof' => 'Pesanan ini belum dapat menerima bukti pembayaran.']);
        }

        $pendingPayment = $order->payments()->where('is_locked', true)->exists();
        if ($pendingPayment) {
            throw ValidationException::withMessages(['proof' => 'Bukti pembayaran sedang menunggu verifikasi admin.']);
        }
        if ($order->refunds()->whereIn('status', [
            RefundStatus::REQUESTED->value,
            RefundStatus::PROCESSING->value,
        ])->exists()) {
            throw ValidationException::withMessages(['proof' => 'Pesanan sedang dalam proses refund dan tidak dapat menerima pembayaran baru.']);
        }

        $verifiedAmount = (float) $order->payments()->where('status', 'verified')->sum('amount');
        $amount = max(0, (float) $order->total - $verifiedAmount);
        $isDownPayment = $order->payment_scheme === 'dp' && $order->status === OrderStatus::MENUNGGU_PEMBAYARAN->value;
        if ($isDownPayment) {
            $amount = min($amount, (float) $order->total / 2);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['proof' => 'Tidak ada tagihan tersisa untuk dibayar.']);
        }

        $disk = Storage::disk('local');
        $directory = "payment-proofs/{$order->order_no}";
        $filename = "{$order->order_no}-".($isDownPayment ? 'dp' : ($verifiedAmount > 0 ? 'settlement' : 'full')).'-'.strtolower($order->payment_method).'-'.Str::uuid().'.'.$proof->extension();
        $path = $disk->putFileAs($directory, $proof, $filename);
        if (! $path) {
            throw new RuntimeException('Gagal menyimpan bukti pembayaran.');
        }

        try {
            return DB::transaction(function () use ($order, $amount, $path, $isDownPayment): Payment {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
                if (! in_array($lockedOrder->status, [
                    OrderStatus::MENUNGGU_PEMBAYARAN->value,
                    OrderStatus::MENUNGGU_PELUNASAN->value,
                ], true)
                    || $lockedOrder->payments()->where('is_locked', true)->exists()
                    || $lockedOrder->refunds()->whereIn('status', [
                        RefundStatus::REQUESTED->value,
                        RefundStatus::PROCESSING->value,
                    ])->exists()) {
                    throw ValidationException::withMessages(['proof' => 'Tagihan berubah atau bukti lain sedang diverifikasi.']);
                }

                $payment = $lockedOrder->payments()->create([
                    'type' => $isDownPayment ? 'dp' : ($lockedOrder->status === OrderStatus::MENUNGGU_PELUNASAN->value ? 'settlement' : 'full'),
                    'amount' => $amount,
                    'method' => $lockedOrder->payment_method,
                    'proof_path' => $path,
                    'is_locked' => true,
                    'status' => 'pending',
                ]);
                $oldStatus = $lockedOrder->status;
                $lockedOrder->status = OrderStatus::DITAHAN->value;
                $lockedOrder->save();
                OrderStatusLog::create([
                    'order_id' => $lockedOrder->id,
                    'status' => OrderStatus::DITAHAN->value,
                    'note' => "Bukti pembayaran {$payment->type} diunggah; menunggu verifikasi.",
                    'changed_by' => $lockedOrder->user_id,
                    'meta' => ['previous_status' => $oldStatus, 'payment_id' => $payment->id],
                ]);

                AdminNotification::create([
                    'type' => 'payment_proof',
                    'title' => 'Bukti pembayaran baru',
                    'message' => "Bukti {$payment->type} untuk {$lockedOrder->order_no} menunggu verifikasi.",
                    'link_url' => route('office.orders.show', $lockedOrder),
                    'meta' => ['order_id' => $lockedOrder->id, 'payment_id' => $payment->id],
                ]);

                return $payment;
            });
        } catch (\Throwable $exception) {
            if (! $disk->delete($path)) {
                Log::error('Failed to remove payment proof after failed database write.', [
                    'order_id' => $order->id,
                    'path' => $path,
                    'exception' => $exception->getMessage(),
                ]);
            }
            throw $exception;
        }
    }

    public function verify(Payment $payment, int $adminId): void
    {
        DB::transaction(function () use ($payment, $adminId): void {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            if (! $payment->isPending() || ! $payment->is_locked || ! $payment->proof_path) {
                throw ValidationException::withMessages(['payment' => 'Bukti pembayaran ini tidak lagi menunggu verifikasi.']);
            }
            if (! $payment->proof_path || ! Storage::disk('local')->exists($payment->proof_path)) {
                throw ValidationException::withMessages(['payment' => 'File bukti pembayaran tidak ditemukan.']);
            }

            $payment->update([
                'status' => 'verified',
                'is_locked' => false,
                'verified_at' => now(),
                'verified_by' => $adminId,
                'rejection_reason' => null,
            ]);
            $user = $order->user()->lockForUpdate()->firstOrFail();
            $customer = Customer::query()->lockForUpdate()->firstOrNew(['user_id' => $user->id]);
            $stateChanged = ! $customer->exists || $customer->current_state !== UserState::MVP->value;
            if ($stateChanged) {
                $customer->current_state = UserState::MVP->value;
                $customer->state_updated_at = now();
            }
            if (! $customer->last_order_at || $order->created_at?->gt($customer->last_order_at)) {
                $customer->last_order_at = $order->created_at ?? now();
            }
            if ($customer->unblocked_until) {
                $customer->unblocked_until = null;
            }
            $customer->save();
            $user->crush()->delete();

            $paid = (float) $order->payments()->where('status', 'verified')->sum('amount');
            $target = $paid >= (float) $order->total
                && $payment->type === 'settlement'
                    ? OrderStatus::SEDANG_DIKEMAS
                    : OrderStatus::PEMBAYARAN_DITERIMA;
            $order->status = $target->value;
            $order->save();
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $target->value,
                'note' => "Pembayaran {$payment->type} diverifikasi admin.",
                'changed_by' => $adminId,
                'meta' => ['payment_id' => $payment->id],
            ]);

            UserNotificationService::push(
                $order->user_id,
                NotificationType::PAYMENT,
                'Pembayaran diterima',
                "Pembayaran untuk pesanan {$order->order_no} telah diverifikasi.",
                route('orders.show', $order),
                meta: ['order_id' => $order->id, 'payment_id' => $payment->id],
            );
        });
    }

    public function reject(Payment $payment, int $adminId, string $reason): void
    {
        DB::transaction(function () use ($payment, $adminId, $reason): void {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $order = Order::query()->lockForUpdate()->findOrFail($payment->order_id);
            if (! $payment->isPending() || ! $payment->is_locked) {
                throw ValidationException::withMessages(['payment' => 'Bukti pembayaran ini tidak lagi menunggu verifikasi.']);
            }

            $payment->update([
                'status' => 'rejected',
                'is_locked' => false,
                'rejection_reason' => $reason,
            ]);
            $nextStatus = $payment->type === 'settlement'
                ? OrderStatus::MENUNGGU_PELUNASAN
                : OrderStatus::MENUNGGU_PEMBAYARAN;
            $order->status = $nextStatus->value;
            $order->save();
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $nextStatus->value,
                'note' => "Bukti pembayaran ditolak: {$reason}",
                'changed_by' => $adminId,
                'meta' => ['payment_id' => $payment->id, 'rejected' => true],
            ]);
            UserNotificationService::push(
                $order->user_id,
                NotificationType::PAYMENT,
                'Bukti pembayaran perlu diperbaiki',
                "Bukti pembayaran {$order->order_no} ditolak. Alasan: {$reason}",
                route('orders.show', $order),
                meta: ['order_id' => $order->id, 'payment_id' => $payment->id],
            );
        });
    }
}
