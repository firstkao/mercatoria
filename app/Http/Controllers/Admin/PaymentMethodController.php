<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Upload QR image
        if ($request->hasFile('qr_image_path')) {
            $data['qr_image_path'] = $request->file('qr_image_path')->store('payment-methods', 'public');
        }

        $method = PaymentMethod::create($data);
        AdminLog::record($request->user(), 'create', 'PaymentMethod', $method->id, null, $data, 'Tambah metode bayar');

        return redirect()->route('office.payment-methods.index')->with('status', 'Metode bayar dibuat.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $data = $this->validateMethod($request, $paymentMethod);
        $data['is_active'] = $request->boolean('is_active');

        // Upload QR image baru (replace yang lama)
        if ($request->hasFile('qr_image_path')) {
            if ($paymentMethod->qr_image_path) {
                Storage::disk('public')->delete($paymentMethod->qr_image_path);
            }
            $data['qr_image_path'] = $request->file('qr_image_path')->store('payment-methods', 'public');
        } else {
            unset($data['qr_image_path']);
        }

        [$old, $new] = AdminLog::diff($paymentMethod, array_keys($data), $data);
        $paymentMethod->update($data);
        AdminLog::record($request->user(), 'update', 'PaymentMethod', $paymentMethod->id, $old, $new, 'Ubah metode bayar');

        return response()->json(['message' => 'Metode bayar diperbarui.']);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        // Cek masih dipakai di orders
        if ($paymentMethod->orders()->exists()) {
            return response()->json([
                'message' => 'Metode "'.$paymentMethod->name.'" masih dipakai di order.',
            ], 422);
        }

        // Hapus file QR
        if ($paymentMethod->qr_image_path) {
            Storage::disk('public')->delete($paymentMethod->qr_image_path);
        }

        AdminLog::record($request->user(), 'delete', 'PaymentMethod', $paymentMethod->id,
            $paymentMethod->only(['code', 'name', 'type', 'fee', 'bank_name', 'account_number', 'account_name', 'qr_image_path', 'instructions', 'is_active']), null, 'Hapus metode bayar');
        $paymentMethod->delete();

        return response()->json(['message' => 'Metode bayar dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMethod(Request $request, ?PaymentMethod $method = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', $method ? 'unique:payment_methods,code,'.$method->id : 'unique:payment_methods,code'],
            'name' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'in:bank_transfer,qris'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:100'],
            'qr_image_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'instructions' => ['nullable', 'string'],
        ]);
    }
}
