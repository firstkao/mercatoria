<?php

namespace App\Http\Controllers\Admin;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Notification\UserNotificationService;
use App\Services\Order\OrderPaymentService;
use App\Services\Order\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderStatusService $statuses,
        private readonly OrderPaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_map(fn (OrderStatus $status) => $status->value, OrderStatus::cases()))],
        ]);
        $orders = Order::query()
            ->with('user')
            ->withCount(['payments as pending_payments_count' => fn ($query) => $query->where('status', 'pending')->where('is_locked', true)])
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(
                fn ($where) => $where->where('order_no', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_email', 'like', "%{$q}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items.snapshot', 'payments.verifiedBy', 'refunds.processedBy', 'statusLogs.changedBy']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(fn (OrderStatus $status) => $status->value, OrderStatus::cases()))],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->statuses->changeStatus(
            $order,
            OrderStatus::from($validated['status']),
            $validated['note'] ?? null,
            $request->user()->id,
        );

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'distinct', 'exists:orders,id'],
            'status' => ['required', Rule::in(array_map(fn (OrderStatus $status) => $status->value, OrderStatus::cases()))],
        ]);
        $orders = DB::transaction(function () use ($validated, $request): array {
            $orders = Order::query()->whereIn('id', $validated['order_ids'])->lockForUpdate()->get();
            $status = OrderStatus::from($validated['status']);

            foreach ($orders as $order) {
                if (! $order->canTransitionTo($status)) {
                    throw ValidationException::withMessages([
                        'status' => "Status {$status->label()} tidak dapat diterapkan untuk {$order->order_no}. Tidak ada pesanan yang diubah.",
                    ]);
                }
            }

            foreach ($orders as $order) {
                $this->statuses->changeStatus($order, $status, 'Perubahan massal oleh admin.', $request->user()->id);
            }

            return $orders->all();
        });

        return back()->with('success', count($orders).' status pesanan berhasil diperbarui.');
    }

    public function sendNote(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'note_to_customer' => ['required', 'string', 'max:5000'],
        ]);
        $order->update([
            'note_to_customer' => $validated['note_to_customer'],
            'note_to_customer_updated_at' => now(),
        ]);
        UserNotificationService::push(
            $order->user_id,
            NotificationType::ORDER_NOTE,
            'Ada catatan baru untuk pesanan',
            "Mercatoria mengirim catatan untuk pesanan {$order->order_no}.",
            route('orders.show', $order).'#note-from-admin',
            meta: ['order_id' => $order->id],
        );

        return back()->with('success', 'Catatan berhasil dikirim ke customer.');
    }

    public function verifyPayment(Request $request, Payment $payment): RedirectResponse
    {
        $this->payments->verify($payment, $request->user()->id);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function rejectPayment(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $this->payments->reject($payment, $request->user()->id, $validated['rejection_reason']);

        return back()->with('success', 'Bukti pembayaran ditolak. Customer dapat mengunggah ulang.');
    }

    public function paymentProof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->proof_path && Storage::disk('local')->exists($payment->proof_path), 404);

        return Storage::disk('local')->response($payment->proof_path);
    }
}
