<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    public function __construct(private OrderStatusService $statuses) {}

    public function verify(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        $this->guard($order, $payment);

        $payment->update([
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $this->statuses->changeStatus($order, 'pembayaran_diterima', 'admin', 'Bukti bayar diverifikasi');

        return redirect()->route('orders.show', $order->no_order);
    }

    public function reject(Request $request, Order $order, Payment $payment): RedirectResponse
    {
        $this->guard($order, $payment);

        $payment->update([
            'status' => Payment::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'reject_reason' => $request->input('reason'),
        ]);

        // PRD 17.10: tolak → lock reset, user dapat upload ulang dalam 24 jam.
        $this->statuses->changeStatus($order, 'menunggu_pembayaran', 'admin', 'Bukti bayar ditolak');

        return redirect()->route('orders.show', $order->no_order);
    }

    private function guard(Order $order, Payment $payment): void
    {
        abort_unless($payment->order_id === $order->id, 404);
        abort_unless($payment->status === Payment::STATUS_PENDING, 422);
    }
}
