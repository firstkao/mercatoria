<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UploadPaymentRequest;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Refund;
use App\Services\Order\OrderStatusService;
use App\Services\Order\PaymentProofService;
use App\Services\Payment\RefundService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Riwayat order user (tab Pesanan Saya — PRD 7.16).
     */
    public function index(Request $request): View
    {
        return view('frontend.orders.index', [
            'orders' => Order::where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($this->canAccess($request, $order), 403);

        $order->load(['items.snapshot', 'userVoucher', 'payments', 'statusLogs']);

        $refunds = app(RefundService::class);

        return view('frontend.order.show', [
            'order' => $order,
            'canRequestRefund' => $order->user_id === $request->user()->id && $refunds->blockedReason($order) === null,
            'refundPending' => $order->refunds()->where('status', Refund::STATUS_REQUESTED)->exists(),
        ]);
    }

    public function storePayment(UploadPaymentRequest $request, Order $order, PaymentProofService $proofs): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $proofs->upload($order, $request->file('proof'), $request->user(), app(OrderStatusService::class));

        ActivityLog::record('upload_payment_proof', 'Upload bukti bayar', ['no_order' => $order->no_order]);

        return redirect()->route('orders.show', $order->no_order)
            ->with('status', 'Bukti bayar terkirim. Menunggu verifikasi admin.');
    }

    public function requestRefund(Request $request, Order $order, RefundService $refunds): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        if ($error = $refunds->blockedReason($order)) {
            return back()->withErrors(['reason' => $error]);
        }

        $refunds->requestByCustomer($order, $data['reason'], $request->user());
        ActivityLog::record('request_refund', 'Request refund', ['no_order' => $order->no_order]);

        return redirect()->route('orders.show', $order->no_order)
            ->with('status', 'Permintaan refund terkirim. Admin akan memprosesnya.');
    }

    public function invoice(Request $request, Order $order)
    {
        abort_unless($this->canAccess($request, $order), 403);

        $order->load(['items.snapshot', 'user']);

        $pdf = Pdf::loadView('frontend.order.invoice', ['order' => $order])
            ->setPaper('a4');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$order->no_order.'.pdf"',
        ]);
    }

    private function canAccess(Request $request, Order $order): bool
    {
        $user = $request->user();

        return $user && ($user->id === $order->user_id || $user->isAdmin());
    }
}
