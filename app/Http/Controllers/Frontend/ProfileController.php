<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\IndonesiaRegion;
use App\Models\ShareClick;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserVoucher;
use App\Services\User\UserStateService;
use App\Services\User\UserTierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
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

        // DS 10.1: blok Crush/Ex/ONS — Kuota View · Countdown State · WA (tidak tampil untuk MVP).
        $state = app(UserStateService::class);
        $userState = $state->stateName($user);
        $viewLimited = $state->isViewLimited($user);
        $viewUsage = $viewLimited ? $state->viewUsage($user) : null;

        // PRD 7.20: pesan WA prefill sesuai state (PR UI).
        $waMessage = match (true) {
            $viewLimited => 'Halo Admin, saya mau minta reset view produk.',
            $userState === 'blocked' => 'Halo Admin, saya mau minta unblock akun.',
            default => null,
        };
        $waUrl = setting('url_whatsapp');
        $waUrlPrefill = $waMessage && $waUrl
            ? $waUrl.(str_contains((string) $waUrl, '?') ? '&' : '?').'text='.rawurlencode($waMessage)
            : null;

        return view('frontend.profile.show', [
            'user' => $user,
            'stateBadge' => $customer?->current_state ?? ucfirst($user->role),
            'userState' => $userState,
            'kuotaViewUsed' => $viewUsage['used'] ?? null,
            'kuotaViewTotal' => $viewUsage['total'] ?? null,
            'countdownState' => $viewLimited ? $state->countdownLabel($user) : null,
            'waUrl' => $viewLimited ? setting('url_whatsapp') : null,
            'waUrlPrefill' => $waUrlPrefill,
            'tierBadge' => $customer ? 'MVP '.ucfirst($customer->tier) : null,
            // PRD 50: progress bar antara batas tier saat ini dan berikutnya.
            'tierProgress' => array_merge([
                'belanja' => (float) ($customer->total_belanja ?? 0),
                'gold' => (float) setting('tier_gold_threshold', 2000000),
                'platinum' => (float) setting('tier_platinum_threshold', 5000000),
            ], $customer
                ? app(UserTierService::class)->progress($customer)
                : ['current_tier' => null, 'next_tier' => 'silver', 'progress_pct' => 0.0, 'belanja_sisa' => null]),
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
            'provinsi_id' => ['nullable', 'integer'],
            'kota' => ['required', 'string', 'max:100'],
            'kota_id' => ['nullable', 'integer'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kecamatan_id' => ['nullable', 'integer'],
            'alamat' => ['required', 'string', 'max:500'],
            'kode_pos' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
        ]);

        // Dropdown berantai alamat: rantai provinsi -> kota -> kecamatan harus valid.
        $chainErrors = IndonesiaRegion::chainErrors(
            $data['provinsi_id'] ?? null,
            $data['kota_id'] ?? null,
            $data['kecamatan_id'] ?? null
        );
        if ($chainErrors !== []) {
            throw ValidationException::withMessages($chainErrors);
        }

        // Nama wilayah diisi dari tabel bila id dikirim (dropdown berantai).
        $regionNames = IndonesiaRegion::namesFor(
            $data['provinsi_id'] ?? null,
            $data['kota_id'] ?? null,
            $data['kecamatan_id'] ?? null
        );

        // tgl_lahir & kode undangan terkunci permanen (PRD 7.6).
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => normalize_phone($data['no_hp']),
            'provinsi' => $regionNames['provinsi'] ?? $data['provinsi'],
            'provinsi_id' => $data['provinsi_id'] ?? null,
            'kota' => $regionNames['kota'] ?? $data['kota'],
            'kota_id' => $data['kota_id'] ?? null,
            'kecamatan' => $regionNames['kecamatan'] ?? $data['kecamatan'],
            'kecamatan_id' => $data['kecamatan_id'] ?? null,
            'alamat' => $data['alamat'],
            'kode_pos' => $data['kode_pos'],
        ]);

        ActivityLog::record('edit_profile', 'Ubah profil', ['fields' => array_keys($data)]);

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

        ActivityLog::record('change_password', 'Ubah kata sandi');

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
            'vouchers' => UserVoucher::with('voucher')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function referral(Request $request): View
    {
        $user = $request->user();
        $referrals = $user->referrals()->orderByDesc('id')->get();

        // DS 10.3: "Berhasil" = referee yang sudah menyelesaikan order pertama (PRD 9.10).
        $berhasil = $user->referrals()
            ->whereHas('orders', fn ($q) => $q->where('status', 'selesai'))
            ->count();

        return view('frontend.profile.referral', [
            'user' => $user,
            'referrals' => $referrals,
            'referralStats' => [
                'total_diundang' => $referrals->count(),
                'berhasil' => $berhasil,
                'menunggu_order' => $referrals->count() - $berhasil,
                'koin_didapat' => (int) CoinTransaction::where('user_id', $user->id)
                    ->where('type', CoinTransaction::TYPE_EARNED_REFERRAL_REFERRER)
                    ->sum('amount'),
            ],
            'shareStats' => [
                'opens' => ShareClick::where('user_id', $user->id)->count(),
                'coins' => (int) CoinTransaction::where('user_id', $user->id)
                    ->where('type', CoinTransaction::TYPE_EARNED_SHARE)
                    ->sum('amount'),
            ],
        ]);
    }

    /**
     * PRD 7.21: modal edukasi ditandai sudah dilihat (1x seumur hidup).
     */
    public function markTourSeen(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['has_seen_tour' => true])->save();

        return back();
    }

    /**
     * PRD 43.6: halaman semua notifikasi (20/halaman).
     */
    public function notifications(Request $request): View
    {
        return view('frontend.profile.notifications', [
            'notifications' => UserNotification::where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    /**
     * PRD 43.8: polling badge bell real-time.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => UserNotification::where('user_id', $request->user()->id)
                ->where('is_read', false)
                ->count(),
        ]);
    }

    public function readAllNotifications(Request $request): RedirectResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->back();
    }
}
