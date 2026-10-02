<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AnonymizeCustomer;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\IdentityRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('peran'));
        $search = trim((string) $request->query('q'));
        $digits = preg_replace('/\D+/', '', $search);

        $users = $this->withTracking(User::query())
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->when(strlen((string) $digits) >= 4, fn ($q) => $q->orWhere('whatsapp', 'like', '%'.ltrim((string) $digits, '0').'%'))))
            ->latest('registered_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'counts' => [
                'semua' => User::count(),
                'spammer' => User::where('role', UserRole::Spammer)->count(),
                'customer' => User::where('role', UserRole::Customer)->count(),
            ],
            'filters' => ['q' => $search, 'peran' => $role?->value],
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }

    // ============================================================
    // TAMBAHAN: CRUD
    // ============================================================

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'whatsapp'  => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8'],
            'role'      => ['required', Rule::enum(UserRole::class)],
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['registered_at'] = now();

        $user = User::create($validated);

        AdminLog::record('create_user', $user, ['nama' => $user->full_name]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user'  => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp'  => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['required', Rule::enum(UserRole::class)],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        AdminLog::record('update_user', $user, ['nama' => $user->full_name]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // orders.user_id = cascadeOnDelete: menghapus user ikut MENGHAPUS PERMANEN semua order,
        // bukti bayar, dan riwayat statusnya (laporan omzet ikut berubah). Untuk customer yang
        // sudah punya order, pakai "Anonimkan" supaya riwayat order tetap tersimpan.
        // (Guard lama membandingkan id admin dengan id user; itu dua tabel berbeda, jadi dihapus.)
        $orderCount = $user->orders()->count();

        if ($orderCount > 0) {
            return back()->withErrors([
                'user' => "Pengguna ini punya {$orderCount} order. Menghapusnya akan ikut menghapus permanen seluruh order, bukti bayar, dan riwayat statusnya. Untuk customer, gunakan tombol Anonimkan agar riwayat order tetap tersimpan.",
            ]);
        }

        $name = $user->full_name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Pengguna {$name} berhasil dihapus.");
    }

    // ============================================================
    // METHOD YANG SUDAH ADA (tetap)
    // ============================================================

    public function show(User $user): View
    {
        $user = $this->withTracking(User::query())->findOrFail($user->id);

        return view('admin.users.show', [
            'user' => $user,
            'activities' => $user->activityLogs()->latest('id')->limit(20)->get(),
            'identities' => IdentityRecord::query()
                ->where(fn ($query) => $query
                    ->where(fn ($q) => $q->where('kind', IdentityRecord::KIND_EMAIL)->where('value', $user->email))
                    ->orWhere(fn ($q) => $q->where('kind', IdentityRecord::KIND_WHATSAPP)->where('value', $user->whatsapp)))
                ->get(),
            'viewQuota' => Setting::integer('view_quota', 10),
            'limit' => Setting::integer('registration_limit', 3),
        ]);
    }

    public function resetQuota(User $user): RedirectResponse
    {
        $before = $user->view_quota_used;
        $user->forceFill(['view_quota_used' => 0])->save();
        AdminLog::record('reset_quota', $user, ['nama' => $user->full_name, 'terpakai sebelumnya' => $before]);

        return back()->with('status', 'Kuota lihat produk direset.');
    }

    // ============================================================
    // PERMINTAAN USER #2: admin perlu tempat yang jelas untuk
    // "membuka kunci" akun spammer. Selama ini tombol reset kuota
    // hanya muncul di halaman detail user (kalau tahu URL-nya), dan
    // tidak ada cara cepat mengembalikan peran spammer -> customer.
    // ============================================================

    /** Pulihkan spammer menjadi customer biasa: kuota direset + tanggal hapus dicabut. */
    public function unlock(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Spammer, 422, 'Pengguna ini bukan spammer.');

        // Status hanya berubah jadi customer jika ada pembelian terverifikasi.
        // (BUG 3 STRICT MODE) Tombol "Buka Kunci" tidak boleh lagi menaikkan
        // peran spammer -> customer secara cuma-cuma; user HARUS tetap spammer
        // sampai punya minimal 1 order selesai/terverifikasi pembayaran.
        if (! $user->hasVerifiedPurchase()) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'Tidak bisa membuka kunci: user ini belum punya pembelian terverifikasi (order selesai / pembayaran diterima). Gunakan Reset Kuota bila hanya ingin menyegarkan kuota — status tetap Spammer.');
        }

        $user->forceFill([
            'role' => UserRole::Customer,
            'view_quota_used' => 0,
            'expires_at' => null,
        ])->save();

        AdminLog::record('unlock_spammer', $user, ['nama' => $user->full_name]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'Akun dibuka: '.e($user->full_name).' kembali jadi Customer dan kuota lihat direset.');
    }

    /** Perpanjang masa tunggu spammer +30 hari (bukan "unlock"; unlock = ubah peran). */
    public function extend(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Spammer, 422, 'Pengguna ini bukan spammer.');

        $user->forceFill(['expires_at' => now()->addDays(30)])->save();
        AdminLog::record('extend_spammer', $user, ['nama' => $user->full_name, 'perpanjangan' => '30 hari']);

        return back()->with('status', 'Masa tunggu spammer diperpanjang 30 hari dari sekarang.');
    }

    public function anonymize(User $user, AnonymizeCustomer $anonymizeCustomer): RedirectResponse
    {
        abort_unless($user->role === UserRole::Customer && ! $user->anonymized_at, 404);

        $anonymizeCustomer->handle($user);
        // Jangan catat nama di log: itu akan menyimpan data pribadi yang baru saja dihapus.
        AdminLog::record('anonymize_customer', $user);

        return redirect()->route('admin.users.show', $user)->with('status', 'Data pribadi customer dihapus. Riwayat order tetap tersimpan.');
    }
    
        /**
     * Kirim link reset password ke user (dipicu admin).
     */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        if (empty($user->email)) {
            return back()->withErrors(['user' => 'User ini tidak punya email (mungkin sudah dianonimkan).']);
        }

        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            AdminLog::record('send_password_reset', $user, ['email' => $user->email]);
            return back()->with('status', "Link reset password sudah dikirim ke {$user->email}.");
        }

        return back()->withErrors(['user' => 'Gagal mengirim link: ' . __($status)]);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function withTracking(Builder $query): Builder
    {
        return $query
            ->select('users.*')
            ->withCount('activityLogs')
            ->withMax(['activityLogs as last_login_at' => fn ($logs) => $logs->where('action', 'login')], 'created_at')
            ->addSelect([
                'orders_count' => DB::table('orders')->selectRaw('count(*)')->whereColumn('orders.user_id', 'users.id'),
                'email_deletions' => IdentityRecord::query()->select('deletion_count')
                    ->where('kind', IdentityRecord::KIND_EMAIL)->whereColumn('value', 'users.email')->limit(1),
            ]);
    }
}
