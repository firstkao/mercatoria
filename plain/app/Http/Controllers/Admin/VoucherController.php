<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::latest()->paginate(20);
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.vouchers.form', ['voucher' => new Voucher(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateVoucher($request);
        $voucher = Voucher::create($data);
        AdminLog::record('create_voucher', $voucher, ['code' => $voucher->code]);

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.form', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validateVoucher($request, $voucher->id);
        $voucher->update($data);
        AdminLog::record('update_voucher', $voucher, ['code' => $voucher->code]);

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $code = $voucher->code;
        $voucher->delete();
        AdminLog::record('delete_voucher', null, ['code' => $code]);

        return redirect()->route('admin.vouchers.index')->with('status', 'Voucher dihapus.');
    }

    private function validateVoucher(Request $request, $id = null)
    {
        return $request->validate([
            'code' => 'required|string|unique:vouchers,code,' . $id,
            'name' => 'required|string',
            'discount_type' => 'required|in:nominal,percent',
            'value' => 'required|integer|min:1',
            'max_discount_idr' => 'nullable|integer|min:1',
            'min_purchase_idr' => 'required|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);
    }
}
