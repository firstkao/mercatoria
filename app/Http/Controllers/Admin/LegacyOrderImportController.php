<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Order\OrderItemSnapshotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LegacyOrderImportController extends Controller
{
    public function __construct(private readonly OrderItemSnapshotService $snapshots) {}

    public function create(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'customer')->where('is_migrated', true),
            ],
        ]);

        $usersQuery = User::query()
            ->where('role', 'customer')
            ->where('is_migrated', true)
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(
                fn ($where) => $where->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%"),
            ))
            ->orderBy('name');
        $users = $usersQuery->limit(100)->get();
        if (! empty($filters['user_id']) && ! $users->contains('id', $filters['user_id'])) {
            $selectedUser = User::query()
                ->where('role', 'customer')
                ->where('is_migrated', true)
                ->find($filters['user_id']);
            if ($selectedUser) {
                $users->push($selectedUser);
            }
        }

        return view('admin.orders.import', [
            'users' => $users,
            'products' => Product::query()->with('variants')->orderBy('name')->get(),
            'selectedUserId' => $filters['user_id'] ?? null,
            'search' => $filters['q'] ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'customer')->where('is_migrated', true),
            ],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $request->input('product_id')),
            ],
            'qty' => ['required', 'integer', 'min:1', 'max:10000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'ordered_at' => ['required', 'date', 'before_or_equal:now'],
        ]);

        [$order, $user] = DB::transaction(function () use ($validated, $request): array {
            $user = User::query()->where('role', 'customer')->where('is_migrated', true)->findOrFail($validated['user_id']);
            if (blank($user->no_hp)) {
                throw ValidationException::withMessages([
                    'user_id' => 'Customer belum memiliki nomor HP. Lengkapi data customer sebelum mengimpor order lama.',
                ]);
            }

            $product = Product::query()->with(['games.developers', 'tags'])->findOrFail($validated['product_id']);
            $variant = isset($validated['variant_id'])
                ? ProductVariant::query()->with('combinations')->findOrFail($validated['variant_id'])
                : null;
            $orderedAt = now()->parse($validated['ordered_at']);
            $unitPrice = (float) $validated['unit_price'];
            $subtotal = $unitPrice * $validated['qty'];
            $address = collect([
                $user->alamat_lengkap,
                $user->kecamatan,
                $user->kota,
                $user->provinsi,
                $user->kode_pos,
            ])->filter()->join(', ');

            $order = Order::create([
                'order_no' => 'OLD-'.Str::upper(Str::random(16)),
                'user_id' => $user->id,
                'status' => OrderStatus::SELESAI->value,
                'payment_scheme' => 'fp',
                'platform' => 'legacy',
                'payment_method' => 'legacy_import',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->no_hp,
                'customer_address' => $address,
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'admin_note' => 'Order lama hasil migrasi. Rincian ongkir historis tidak tersedia.',
            ]);
            $order->forceFill(['created_at' => $orderedAt, 'updated_at' => $orderedAt])->save();

            $item = $order->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'qty' => $validated['qty'],
            ]);

            $snapshot = $this->snapshots->createForLegacyOrderItem($item, $order->order_no, $unitPrice, $orderedAt);
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => OrderStatus::SELESAI->value,
                'note' => 'Order lama diimpor dari WooCommerce sebagai transaksi selesai.',
                'changed_by' => $request->user()->id,
                'meta' => ['legacy_import' => true, 'snapshot_id' => $snapshot->id],
            ]);

            AdminLog::record(
                'legacy_order_import',
                Order::class,
                $order->id,
                new: ['order_no' => $order->order_no, 'snapshot_id' => $snapshot->id],
                description: 'Admin mengimpor order lama dari WooCommerce.',
            );

            return [$order, $user];
        });

        return redirect()->route('office.users.show', $user)
            ->with('success', "Order lama {$order->order_no} berhasil disimpan sebagai snapshot.");
    }
}
