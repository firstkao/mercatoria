<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVoucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Dashboard user (PRD 7.16 / Design Spec 10.1): header + 4 stats + tab + Tagihan Menunggu (18.4).
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $customer = $user->customer;

        return view('frontend.profile.show', [
            'user' => $user,
            'stateBadge' => $customer?->current_state ?? ucfirst($user->role),
            'tierBadge' => $customer ? 'MVP '.ucfirst($customer->tier) : null,
            'tagihan' => $user->orders()
                ->whereIn('status', ['menunggu_pembayaran', 'menunggu_pelunasan_pengambilan'])
                ->orderByDesc('id')
                ->get(),
            'orders' => $user->orders()->orderByDesc('id')->limit(10)->get(),
            'totalOrders' => $user->orders()->count(),
            'totalBelanja' => (float) $user->orders()->where('status', 'selesai')->sum('total'),
            'coinBalance' => (int) Coin::where('user_id', $user->id)->value('balance'),
            'referralCount' => $user->referrals()->count(),
            // "Berhasil" = referee yang sudah menyelesaikan order pertama (PRD 9.10).
            'undangBerhasil' => $user->referrals()
                ->whereHas('orders', fn ($q) => $q->where('status', 'selesai'))
                ->count(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('frontend.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'no_hp' => ['required', 'regex:/^(?:\+?62|0)8[1-9][0-9]{6,11}$/'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:500'],
            'kode_pos' => ['required', 'string', 'max:10'],
        ]);

        // tgl_lahir & kode undangan terkunci permanen (PRD 7.6).
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => normalize_phone($data['no_hp']),
            'provinsi' => $data['provinsi'],
            'kota' => $data['kota'],
            'kecamatan' => $data['kecamatan'],
            'alamat' => $data['alamat'],
            'kode_pos' => $data['kode_pos'],
        ]);

        return redirect()->route('profile.edit')->with('status', 'Profil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('profile.edit')->with('status', 'Kata sandi diganti.');
    }

    public function complete(Request $request): View
    {
        return view('frontend.profile.complete', ['user' => $request->user()]);
    }

    public function storeComplete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tgl_lahir' => ['required', 'date'],
        ]);

        $request->user()->update(['tgl_lahir' => $data['tgl_lahir']]);

        return redirect()->route('dashboard')->with('status', 'Profil dilengkapi.');
    }

    public function coins(Request $request): View
    {
        $user = $request->user();

        return view('frontend.profile.coins', [
            'user' => $user,
            'balance' => (int) Coin::where('user_id', $user->id)->value('balance'),
            'transactions' => CoinTransaction::where('user_id', $user->id)->orderByDesc('id')->get(),
        ]);
    }

    public function wallet(Request $request): View
    {
        return view('frontend.profile.wallet', [
            'userVouchers' => UserVoucher::with('voucher')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function referral(Request $request): View
    {
        return view('frontend.profile.referral', [
            'user' => $request->user(),
            'referrals' => $request->user()->referrals()->orderByDesc('id')->get(),
        ]);
    }
}
