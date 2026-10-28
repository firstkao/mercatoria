<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderStatusService $statuses) {}

    public function index(Request $request): View
    {
        return view('admin.orders.index', [
            'orders' => Order::with('user')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.snapshot', 'payments', 'statusLogs', 'user', 'userVoucher']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'no_orders' => ['required', 'array', 'min:1'],
            'no_orders.*' => ['string', 'exists:orders,no_order'],
        ]);

        foreach ($data['no_orders'] as $noOrder) {
            $order = Order::where('no_order', $noOrder)->first();
            [$old, $new] = AdminLog::diff($order, ['status'], ['status' => $data['status']]);
            $this->statuses->changeStatus($order, $data['status'], 'admin', 'Bulk update status');
            AdminLog::record($request->user(), 'update', 'Order', $order->id, $old, $new, 'Bulk ubah status order');
        }

        return redirect()->route('office.orders.index')->with('status', count($data['no_orders']).' order diperbarui.');
    }

    public function storeSettlement(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'type' => Payment::TYPE_SETTLEMENT,
            'method' => $order->payment_method,
            'amount' => $data['amount'],
            'file_path' => 'manual/settlement-'.$order->no_order,
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'uploaded_at' => now(),
        ]);

        AdminLog::record($request->user(), 'create', 'Payment', $payment->id, null, $data, 'Catat pelunasan settlement');

        return redirect()->route('office.orders.show', $order)->with('status', 'Pelunasan dicatat.');
    }
}
