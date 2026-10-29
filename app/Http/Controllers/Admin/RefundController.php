<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Refund;
use App\Services\Payment\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(private RefundService $refunds) {}

    /**
     * PRD 34: request refund full (manual admin).
     */
    public function requestFull(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $refund = $this->refunds->requestFull($order, $data['reason'], $request->user());
        $this->log($request, 'refund_full', $order, $refund);

        return redirect()->route('office.orders.show', $order)->with('status', 'Refund full diminta.');
    }

    /**
     * PRD 34: request refund partial (nominal ditentukan admin).
     */
    public function requestPartial(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $refund = $this->refunds->requestPartial($order, (float) $data['amount'], $data['reason'], $request->user());
        $this->log($request, 'refund_partial', $order, $refund);

        return redirect()->route('office.orders.show', $order)->with('status', 'Refund partial diminta.');
    }

    public function complete(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== Refund::STATUS_REQUESTED) {
            return back()->withErrors(['refund' => 'Refund sudah diproses sebelumnya.']);
        }

        $this->refunds->complete($refund, $request->user());
        $this->log($request, 'refund_complete', $refund->order, $refund);

        return redirect()->route('office.orders.show', $refund->order)->with('status', 'Refund selesai dikerjakan.');
    }

    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        if ($refund->status !== Refund::STATUS_REQUESTED) {
            return back()->withErrors(['refund' => 'Refund sudah diproses sebelumnya.']);
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->refunds->reject($refund, $data['rejection_reason'], $request->user());
        $this->log($request, 'refund_reject', $refund->order, $refund);

        return redirect()->route('office.orders.show', $refund->order)->with('status', 'Refund ditolak.');
    }

    private function log(Request $request, string $action, Order $order, Refund $refund): void
    {
        AdminLog::record($request->user(), $action, 'Refund', $refund->id, null, [
            'order_id' => $order->id,
            'refund_id' => $refund->id,
            'amount' => (float) $refund->amount,
        ], ucfirst(str_replace('_', ' ', $action)));
    }
}
