<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderStatusFormRequest;
use App\Models\AdminLog;
use App\Models\Order;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\RedirectResponse;

class OrderStatusController extends Controller
{
    public function __construct(private OrderStatusService $statuses) {}

    public function update(OrderStatusFormRequest $request, Order $order): RedirectResponse
    {
        $validated = $request->validated();

        [$old, $new] = AdminLog::diff($order, ['status'], ['status' => $validated['status']]);
        $this->statuses->changeStatus($order, $validated['status'], 'admin', $validated['note'] ?? null);
        AdminLog::record($request->user(), 'update', 'Order', $order->id, $old, $new, 'Ubah status order');

        return redirect()->route('office.orders.show', $order->no_order);
    }
}
