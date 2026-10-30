<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-methods.index', [
            'methods' => PaymentMethod::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateMethod($request);
        $data['is_active'] = $request->boolean('is_active');

        $method = PaymentMethod::create($data);
        AdminLog::record($request->user(), 'create', 'PaymentMethod', $method->id, null, $data, 'Tambah metode bayar');

        return redirect()->route('office.payment-methods.index')->with('status', 'Metode bayar dibuat.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $this->validateMethod($request, $paymentMethod);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($paymentMethod, array_keys($data), $data);
        $paymentMethod->update($data);
        AdminLog::record($request->user(), 'update', 'PaymentMethod', $paymentMethod->id, $old, $new, 'Ubah metode bayar');

        return redirect()->route('office.payment-methods.index')->with('status', 'Metode bayar diperbarui.');
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'PaymentMethod', $paymentMethod->id, $paymentMethod->only(['code', 'name', 'type', 'fee']), null, 'Hapus metode bayar');
        $paymentMethod->delete();

        return redirect()->route('office.payment-methods.index')->with('status', 'Metode bayar dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMethod(Request $request, ?PaymentMethod $method = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', $method ? 'unique:payment_methods,code,'.$method->id : 'unique:payment_methods,code'],
            'name' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:bank_transfer,qr_code'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string'],
        ]);
    }
}
