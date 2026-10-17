<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $term = trim($validated['q']);
        $like = '%'.$term.'%';

        return response()->json([
            'products' => Product::query()
                ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('sku', 'like', $like))
                ->orderBy('name')->limit(5)->get(['id', 'name', 'sku'])
                ->map(fn (Product $product) => [
                    'label' => "{$product->name} ({$product->sku})",
                    'url' => route('office.products.edit', $product),
                ]),
            'orders' => Order::query()
                ->where(fn ($query) => $query->where('order_no', 'like', $like)->orWhere('customer_email', 'like', $like))
                ->latest('id')->limit(5)->get(['id', 'order_no', 'customer_email'])
                ->map(fn (Order $order) => [
                    'label' => "{$order->order_no} ({$order->customer_email})",
                    'url' => route('office.orders.show', $order),
                ]),
            'users' => User::query()
                ->where(fn ($query) => $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('no_hp', 'like', $like))
                ->orderBy('name')->limit(5)->get(['id', 'name', 'email', 'no_hp'])
                ->map(fn (User $user) => [
                    'label' => "{$user->name} ({$user->email})",
                    'url' => route('office.users.show', $user),
                ]),
        ]);
    }
}
