<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, string $orderNumber): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order->load([
            'items.variant.product',
            'paymentProofs.method',
            'marketplace',
            'statusHistory.admin',
        ]);

        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('orders.show', compact('order', 'paymentMethods'));
    }

    public function proof(Request $request, string $orderNumber): RedirectResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        abort_unless(
            in_array($order->status, ['menunggu_pembayaran', 'pembayaran_gagal'], true),
            422,
            'Status pesanan tidak memungkinkan upload bukti.'
        );

        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $file = $request->file('proof');
        $filename = 'payment-proofs/' . Str::random(40) . '.webp';

        $manager = new ImageManager(new Driver());
        $img = $manager->read($file)->scaleDown(width: 800);
        Storage::disk('public')->put($filename, (string) $img->toWebp(75));

        $order->paymentProofs()->create([
            'payment_method_id' => $request->input('payment_method_id'),
            'amount_idr' => $order->pay_now_idr,
            'proof_path' => $filename,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        $order->update(['status' => 'ditahan']);

        ActivityLog::record($request->user(), 'upload_payment_proof', $request, [
            'order_number' => $order->order_number,
        ]);

        return redirect()
            ->route('account.orders.show', $orderNumber)
            ->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    public function invoice(Request $request, string $orderNumber): View
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order->load(['items.variant', 'user', 'marketplace', 'paymentProofs.method']);

        return view('orders.print.invoice', [
            'order' => $order,
            'branding' => [
                'store_name' => 'MERCATORIA',
                'store_address' => Setting::get('store_address'),
                'contact_email' => Setting::get('contact_email'),
                'contact_whatsapp' => Setting::get('contact_whatsapp'),
                'has_logo' => file_exists(public_path('images/logo.png')),
                'logo_url' => asset('images/logo.png'),
            ],
            'generatedAt' => now(),
        ]);
    }
}