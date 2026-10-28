<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function __construct(private OrderStatusService $statuses) {}

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $this->statuses->changeStatus($order, $validated['status'], 'admin', $request->input('note'));

        return redirect()->route('orders.show', $order->no_order);
    }
}
