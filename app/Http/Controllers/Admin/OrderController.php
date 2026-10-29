<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Order\LatePenaltyService;
use App\Services\Order\OrderStatusService;
use App\Services\User\UserNotificationService;
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

    /**
     * PRD 44: Note to Customer (1-way, max 1000) — bell only, tanpa email.
     */
    public function noteToCustomer(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'note_to_customer' => ['required', 'string', 'max:1000'],
        ]);

        [$old, $new] = AdminLog::diff($order, ['note_to_customer'], $data);
        $order->update([
            'note_to_customer' => $data['note_to_customer'],
            'note_to_customer_updated_at' => now(),
        ]);

        app(UserNotificationService::class)->notify(
            $order->user,
            'order_note',
            'Pesan baru dari Admin untuk pesanan '.$order->no_order,
            '/orders/'.$order->no_order
        );

        AdminLog::record($request->user(), 'note_to_customer', 'Order', $order->id, $old, $new, 'Kirim Note to Customer');

        return redirect()->route('office.orders.show', $order)->with('status', 'Pesan terkirim ke customer.');
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

    /**
     * PRD 51.1: "Buka Kunci via Denda".
     */
    public function unlockPenalty(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'penalty_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $before = ['status' => $order->status];
        app(LatePenaltyService::class)->unlockViaPenalty(
            $order,
            $request->user(),
            isset($data['penalty_amount']) ? (int) $data['penalty_amount'] : null
        );

        AdminLog::record($request->user(), 'update', 'Order', $order->id,
            $before, $order->fresh()->only(['status', 'penalty_amount']),
            'Buka Kunci via Denda');

        return redirect()->route('office.orders.show', $order)->with('status', 'Order dibuka via denda.');
    }

    /**
     * PRD 51.1: Hangus Permanen.
     */
    public function hangusPermanent(Request $request, Order $order): RedirectResponse
    {
        $before = ['status' => $order->status];
        app(LatePenaltyService::class)->markHangusPermanen($order, $request->user());

        AdminLog::record($request->user(), 'update', 'Order', $order->id,
            $before, ['status' => $order->fresh()->status],
            'Hangus Permanen');

        return redirect()->route('office.orders.show', $order)->with('status', 'Order ditetapkan Hangus Permanen.');
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
