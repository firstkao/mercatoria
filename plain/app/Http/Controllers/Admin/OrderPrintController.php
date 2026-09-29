<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\View\View;

class OrderPrintController extends Controller
{
    public function invoice(Order $order): View
    {
        $order->load(['items.variant', 'user', 'marketplace', 'paymentProofs.method']);

        return view('admin.orders.print.invoice', [
            'order' => $order,
            'branding' => $this->branding(),
            'generatedAt' => now(),
        ]);
    }

    public function packingSlip(Order $order): View
    {
        $order->load(['items.variant.product', 'user', 'marketplace']);

        return view('admin.orders.print.packing-slip', [
            'order' => $order,
            'branding' => $this->branding(),
            'generatedAt' => now(),
        ]);
    }

    /**
     * @return array<string, ?string>
     */
    private function branding(): array
    {
        return [
            'store_name' => 'MERCATORIA',
            'store_address' => Setting::get('store_address'),
            'contact_email' => Setting::get('contact_email'),
            'contact_whatsapp' => Setting::get('contact_whatsapp'),
            'has_logo' => file_exists(public_path('images/logo.png')),
            'logo_url' => asset('images/logo.png'),
        ];
    }
}