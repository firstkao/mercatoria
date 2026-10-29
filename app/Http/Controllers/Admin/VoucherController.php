<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(): View
    {
        return view('admin.vouchers.index', [
            'vouchers' => Voucher::withCount('userVouchers')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateVoucher($request);
        $data['is_active'] = $request->boolean('is_active');

        $voucher = Voucher::create($data);
        AdminLog::record($request->user(), 'create', 'Voucher', $voucher->id, null, $data, 'Buat voucher template');

        return redirect()->route('office.vouchers.index')->with('status', 'Voucher template dibuat.');
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $data = $this->validateVoucher($request, $voucher);
        $data['is_active'] = $request->boolean('is_active');

        [$old, $new] = AdminLog::diff($voucher, array_keys($data), $data);
        $voucher->update($data);
        AdminLog::record($request->user(), 'update', 'Voucher', $voucher->id, $old, $new, 'Ubah voucher template');

        return redirect()->route('office.vouchers.index')->with('status', 'Voucher template diperbarui.');
    }

    public function destroy(Request $request, Voucher $voucher): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'Voucher', $voucher->id, $voucher->only(['code', 'name', 'nominal']), null, 'Hapus voucher template');
        $voucher->delete();

        return redirect()->route('office.vouchers.index')->with('status', 'Voucher template dihapus.');
    }

    /**
     * PRD 22.6: buat user_voucher dari template — masa berlaku dari valid_days.
     */
    public function issue(Request $request, Voucher $voucher): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $userVoucher = UserVoucher::create([
            'user_id' => $data['user_id'],
            'voucher_id' => $voucher->id,
            'status' => UserVoucher::STATUS_ACTIVE,
            'expires_at' => $voucher->valid_days ? now()->addDays($voucher->valid_days) : null,
        ]);

        AdminLog::record($request->user(), 'create', 'UserVoucher', $userVoucher->id, null, [
            'user_id' => $data['user_id'],
            'voucher_id' => $voucher->id,
            'expires_at' => (string) $userVoucher->expires_at,
        ], 'Issue voucher '.$voucher->code);

        return redirect()->route('office.vouchers.index')->with('status', 'Voucher diterbitkan ke user.');
    }

    public function revoke(Request $request, UserVoucher $userVoucher): RedirectResponse
    {
        AdminLog::record($request->user(), 'delete', 'UserVoucher', $userVoucher->id, $userVoucher->only(['user_id', 'voucher_id', 'status']), null, 'Cabut user_voucher');
        $userVoucher->delete();

        return redirect()->route('office.vouchers.index')->with('status', 'User voucher dicabut.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:40', $voucher ? 'unique:vouchers,code,'.$voucher->id : 'unique:vouchers,code'],
            'name' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:20'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'min_belanja' => ['nullable', 'numeric', 'min:0'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);
    }
}
