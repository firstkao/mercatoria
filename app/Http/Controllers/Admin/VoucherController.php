<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(): View
    {
        return view('admin.vouchers.index', [
            'vouchers' => Voucher::query()->withCount('userVouchers')->latest('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.vouchers.form', ['voucher' => new Voucher]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        DB::transaction(function () use ($validated): void {
            $voucher = Voucher::create($validated);
            AdminLog::record('voucher_created', Voucher::class, $voucher->id, new: $validated);
        });

        return redirect()->route('office.vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher): View
    {
        return view('admin.vouchers.form', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $this->validatedData($request, $voucher);
        $old = $voucher->only(array_keys($validated));
        DB::transaction(function () use ($voucher, $validated, $old): void {
            $voucher->update($validated);
            AdminLog::record('voucher_updated', Voucher::class, $voucher->id, old: $old, new: $validated);
        });

        return redirect()->route('office.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->userVouchers()->exists()) {
            return back()->withErrors([
                'voucher' => 'Voucher sudah pernah dibagikan dan tidak dapat dihapus. Nonaktifkan voucher sebagai gantinya.',
            ]);
        }

        DB::transaction(function () use ($voucher): void {
            AdminLog::record('voucher_deleted', Voucher::class, $voucher->id, old: $voucher->only(['code', 'name', 'type', 'value']));
            $voucher->delete();
        });

        return redirect()->route('office.vouchers.index')->with('success', 'Voucher berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $valueRules = ['required', 'numeric', 'gt:0', 'max:999999999999'];
        if ($request->input('type') === 'percent') {
            $valueRules[] = 'lte:100';
        }

        return $request->validate([
            'code' => ['required', 'alpha_dash:ascii', 'max:32', Rule::unique('vouchers', 'code')->ignore($voucher?->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['nominal', 'percent'])],
            'value' => $valueRules,
            'min_purchase' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'max_discount' => ['nullable', 'numeric', 'gt:0', 'max:999999999999'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'per_user_limit' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
