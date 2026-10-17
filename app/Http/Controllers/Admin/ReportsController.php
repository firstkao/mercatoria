<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validateDateFilters($request);
        $payments = Payment::query()
            ->where('status', 'verified')
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $from) => $query->where('verified_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $to) => $query->where('verified_at', '<=', Carbon::parse($to)->endOfDay()));

        $completedOrders = Order::query()
            ->where('status', OrderStatus::SELESAI->value)
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $from) => $query->where('updated_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $to) => $query->where('updated_at', '<=', Carbon::parse($to)->endOfDay()));

        $outstandingOrders = Order::query()
            ->where('payment_scheme', 'dp')
            ->whereNotIn('status', [
                OrderStatus::SELESAI->value,
                OrderStatus::HANGUS->value,
                OrderStatus::DANA_DIKEMBALIKAN->value,
            ])
            ->withSum(['payments as verified_amount' => fn (Builder $query) => $query->where('status', 'verified')], 'amount');

        $metrics = [
            'dp_received' => (clone $payments)->where('type', 'dp')->sum('amount'),
            'settlements_received' => (clone $payments)->where('type', 'settlement')->sum('amount'),
            'full_payments_received' => (clone $payments)->where('type', 'full')->sum('amount'),
            'completed_sales' => (clone $completedOrders)->sum('total'),
            'outstanding' => $outstandingOrders->get()->sum(
                fn (Order $order): float => max(0, (float) $order->total - (float) ($order->verified_amount ?? 0)),
            ),
            'forfeited_received' => (clone $payments)->whereHas(
                'order',
                fn (Builder $query) => $query->where('status', OrderStatus::HANGUS->value),
            )->sum('amount'),
        ];

        return view('admin.reports.index', compact('metrics', 'filters'));
    }

    public function customers(Request $request): View
    {
        $filters = $this->validateCustomerFilters($request);
        $query = $this->rankedCustomers($filters);
        match ($filters['sort'] ?? 'spend') {
            'orders' => $query->orderByDesc('completed_orders')->orderByDesc('total_spend'),
            'aov' => $query->orderByRaw('CASE WHEN completed_orders > 0 THEN total_spend / completed_orders ELSE 0 END DESC'),
            default => $query->orderByDesc('total_spend')->orderByDesc('completed_orders'),
        };
        $customers = $query->orderBy('name')->paginate(30)->withQueryString();

        return view('admin.reports.customers', compact('customers', 'filters'));
    }

    public function exportCustomers(Request $request): StreamedResponse
    {
        $filters = $this->validateCustomerFilters($request);
        $query = $this->rankedCustomers($filters)->orderByDesc('total_spend')->orderBy('users.id');
        $fileName = 'customer-rank-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new \RuntimeException('Unable to open the CSV output stream.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nama', 'Email', 'No. HP', 'Total Pesanan', 'Total Pengeluaran (IDR)', 'AOV (IDR)'], ',', '"', '');
            foreach ($query->cursor() as $customer) {
                $orders = (int) $customer->completed_orders;
                $spend = (float) ($customer->total_spend ?? 0);
                fputcsv($output, [
                    $this->safeSpreadsheetValue($customer->name),
                    $this->safeSpreadsheetValue($customer->email),
                    $this->safeSpreadsheetValue($customer->no_hp),
                    $orders,
                    number_format($spend, 2, '.', ''),
                    number_format($orders > 0 ? $spend / $orders : 0, 2, '.', ''),
                ], ',', '"', '');
            }
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function rankedCustomers(array $filters): Builder
    {
        $completed = fn (Builder $query) => $query->where('status', OrderStatus::SELESAI->value)
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $from) => $query->where('updated_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $to) => $query->where('updated_at', '<=', Carbon::parse($to)->endOfDay()));

        return User::query()
            ->where('role', 'customer')
            ->where(fn (Builder $query) => $query->whereHas('customer')->orWhereDoesntHave('crush'))
            ->when($filters['q'] ?? null, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%"),
            ))
            ->withCount(['orders as completed_orders' => $completed])
            ->withSum(['orders as total_spend' => $completed], 'total');
    }

    private function validateDateFilters(Request $request): array
    {
        return $this->validatedDateRange($request);
    }

    private function validateCustomerFilters(Request $request): array
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'sort' => ['nullable', 'in:spend,orders,aov'],
        ]);

        $this->assertValidDateRange($filters);

        return $filters;
    }

    private function validatedDateRange(Request $request): array
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
        $this->assertValidDateRange($filters);

        return $filters;
    }

    private function assertValidDateRange(array $filters): void
    {
        if (isset($filters['date_from'], $filters['date_to'])
            && Carbon::parse($filters['date_from'])->gt(Carbon::parse($filters['date_to']))) {
            throw ValidationException::withMessages([
                'date_to' => 'Tanggal akhir harus sama dengan atau setelah tanggal awal.',
            ]);
        }
    }

    private function safeSpreadsheetValue(?string $value): string
    {
        return preg_match('/^[=+\-@]/', (string) $value) === 1 ? "'".$value : (string) $value;
    }
}
