<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Cart;
use App\Models\Coin;
use App\Models\PaymentMethod;
use App\Models\UserVoucher;
use App\Services\Cart\CheckoutService;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkout,
        private ShippingCalculatorService $shipping,
    ) {}

    public function show(Request $request): View
    {
        $selectedIds = collect($request->query('items', []))->map(fn ($id) => (int) $id)->all();

        $items = Cart::with(['product.game', 'variant'])
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $selectedIds)
            ->get();

        abort_if($items->isEmpty(), 404);

        return view('frontend.checkout.show', [
            'items' => $items,
            'lines' => $this->buildLines($items),
            'methods' => PaymentMethod::where('is_active', true)->get(),
            'coinBalance' => (int) Coin::where('user_id', $request->user()->id)->value('balance'),
            'maxCoin' => (int) $this->shipping->maxCoinDiscount(
                (float) $items->sum(fn (Cart $c) => $this->shipping->calculateItemShipping($c->product, $c->variant)['final_price_bersih'] * $c->qty)
            ),
            'userVouchers' => UserVoucher::with('voucher')
                ->where('user_id', $request->user()->id)
                ->where('status', UserVoucher::STATUS_ACTIVE)
                ->get(),
        ]);
    }

    /**
     * Ringkasan harga live (PRD 17.3: fee berubah saat pilihan berubah).
     */
    public function summary(CheckoutRequest $request): JsonResponse
    {
        $items = Cart::with(['product', 'variant'])
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $request->input('cart_item_ids'))
            ->get();

        $lines = $this->buildLines($items);
        $subtotal = (float) collect($lines)->sum(fn ($line) => $line['final_price_bersih'] * $line['qty']);

        $discount = 0.0;
        $data = $request->checkoutData();

        if (($data['discount_type'] ?? null) === 'coin') {
            $discount = (float) min((int) $data['coin_amount'], (int) $this->shipping->maxCoinDiscount($subtotal));
        }

        if (($data['discount_type'] ?? null) === 'voucher') {
            $userVoucher = UserVoucher::with('voucher')
                ->where('user_id', $request->user()->id)
                ->find($data['user_voucher_id']);

            if ($userVoucher && $userVoucher->isUsable() && $subtotal >= (float) $userVoucher->voucher->min_belanja) {
                $discount = (float) $userVoucher->voucher->nominal;
            }
        }

        $totals = $this->shipping->calculateCheckoutTotal(
            $lines,
            $discount,
            $data['scheme'],
            $data['platform'],
            $data['payment_method'],
        );

        return response()->json($totals);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->placeOrder(
            $request->user(),
            collect($request->input('cart_item_ids'))->map(fn ($id) => (int) $id)->all(),
            $request->checkoutData(),
        );

        return redirect()->route('orders.show', $order->no_order);
    }

    /**
     * @param  Collection<int, Cart>  $items
     * @return array<int, array{final_price_bersih: float, qty: int}>
     */
    private function buildLines($items): array
    {
        return $items->map(fn (Cart $cart) => [
            'final_price_bersih' => $this->shipping->calculateItemShipping($cart->product, $cart->variant)['final_price_bersih'],
            'qty' => $cart->qty,
        ])->all();
    }
}
