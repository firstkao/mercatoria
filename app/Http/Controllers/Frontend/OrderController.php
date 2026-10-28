<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\UploadPaymentRequest;
use App\Models\Order;
use App\Services\Order\OrderStatusService;
use App\Services\Order\PaymentProofService;
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

        return view('frontend.order.show', ['order' => $order]);
    }

    public function storePayment(UploadPaymentRequest $request, Order $order, PaymentProofService $proofs): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $proofs->upload($order, $request->file('proof'), $request->user(), app(OrderStatusService::class));

        return redirect()->route('orders.show', $order->no_order)
            ->with('status', 'Bukti bayar terkirim. Menunggu verifikasi admin.');
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
