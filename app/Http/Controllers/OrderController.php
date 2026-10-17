<?php

namespace App\Http\Controllers;

use App\Services\Order\OrderPaymentService;
use App\Services\Order\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderPaymentService $payments,
        private readonly RefundService $refunds,
    ) {}

    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['items.snapshot', 'payments'])
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, int $order): View
    {
        $order = $request->user()->orders()
            ->with(['items.snapshot', 'payments', 'refunds', 'statusLogs.changedBy'])
            ->findOrFail($order);
        $ga4Events = [[
            'name' => 'view_order_detail',
            'params' => ['order_id' => $order->order_no],
        ]];
        $purchaseKey = "ga4_purchase_sent_{$order->id}";
        if (
            $order->paidAmount() >= (float) $order->total
            && ! (bool) $request->session()->get($purchaseKey, false)
        ) {
            $ga4Events[] = [
                'name' => 'purchase',
                'params' => [
                    'transaction_id' => $order->order_no,
                    'value' => (float) $order->total,
                    'currency' => 'IDR',
                    'shipping' => (float) $order->shipping_cost,
                    'items' => $order->items->map(fn ($item): array => [
                        'item_id' => $item->snapshot->product_sku,
                        'item_name' => $item->snapshot->product_name,
                        'item_variant' => $item->snapshot->variant_name,
                        'price' => (float) $item->snapshot->final_price,
                        'quantity' => $item->snapshot->qty,
                    ])->values()->all(),
                ],
            ];
            $request->session()->put($purchaseKey, true);
        }

        return view('orders.show', compact('order', 'ga4Events'));
    }

    public function uploadProof(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $order = $request->user()->orders()->findOrFail($order);
        $this->payments->uploadProof($order, $validated['proof']);

        return back()->with('success', 'Bukti pembayaran dikirim dan menunggu verifikasi admin.');
    }

    public function requestRefund(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $order = $request->user()->orders()->findOrFail($order);
        $this->refunds->request($order, $request->user(), (float) $validated['amount'], $validated['reason']);

        return back()->with('success', 'Permintaan refund telah dikirim.');
    }

    public function snapshotImage(Request $request, int $order, int $snapshot, string $image): StreamedResponse
    {
        $order = $request->user()->orders()->findOrFail($order);
        $item = $order->items()->whereHas('snapshot', fn ($query) => $query->whereKey($snapshot))->firstOrFail();
        abort_unless(in_array($image, ['product', 'variant'], true), 404);
        $imagePath = $image === 'variant'
            ? $item->snapshot->variant_image_path
            : $item->snapshot->product_image_path;

        abort_unless($imagePath && Storage::disk('public')->exists($imagePath), 404);

        return Storage::disk('public')->response($imagePath);
    }
}
