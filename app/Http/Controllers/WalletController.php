<?php

namespace App\Http\Controllers;

use App\Models\UserVoucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $vouchers = $user->userVouchers()->with('voucher')->latest('id')->get();

        return view('wallet.index', [
            'balance' => $user->coin?->balance ?? 0,
            'transactions' => $user->coinTransactions()->latest('id')->paginate(30),
            'activeVouchers' => $vouchers->filter(fn (UserVoucher $userVoucher) => $userVoucher->status === 'active'
                && $userVoucher->voucher?->isValid()
                && (! $userVoucher->voucher->valid_until || $userVoucher->voucher->valid_until->gt(now()->addDays(7)))),
            'expiringVouchers' => $vouchers->filter(fn (UserVoucher $userVoucher) => $userVoucher->status === 'active'
                && $userVoucher->voucher?->isValid()
                && $userVoucher->voucher->valid_until
                && $userVoucher->voucher->valid_until->lte(now()->addDays(7))),
            'voucherHistory' => $vouchers->filter(fn (UserVoucher $userVoucher) => $userVoucher->status !== 'active'
                || ! $userVoucher->voucher?->isValid()),
        ]);
    }
}
