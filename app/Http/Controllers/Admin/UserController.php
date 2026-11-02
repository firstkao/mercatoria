<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AdminLog;
use App\Models\Spammer;
use App\Models\User;
use App\Models\VisitorTracking;
use App\Models\Voucher;
use App\Services\User\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = (string) $request->query('q', '');

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(fn ($sub) => $sub->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('no_hp', 'like', $like));
            })
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $blocked = Spammer::query()
            ->where('status', Spammer::STATUS_BLOCKED)
            ->where(fn ($query) => $query->whereIn('email', $users->pluck('email'))
                ->orWhereIn('no_hp', $users->pluck('no_hp')->filter()))
            ->get(['email', 'no_hp']);

        $blockedUserIds = $users
            ->filter(fn (User $user) => $blocked->contains(fn (Spammer $spammer) => $spammer->email === $user->email
                || ($user->no_hp !== null && $spammer->no_hp === $user->no_hp)))
            ->pluck('id')
            ->all();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
            'blockedUserIds' => $blockedUserIds,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        // PRD 48.3: inject user lama — password & tgl lahir boleh kosong.
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'in:customer'],
            'tgl_lahir' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'no_hp' => ['required', 'regex:/^(?:\+?62|0)8[1-9][0-9]{6,11}$/'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:500'],
            'kode_pos' => ['required', 'string', 'max:10'],
        ]);

        $data['no_hp'] = normalize_phone($data['no_hp']);
        $data['role'] = $data['role'] ?? User::ROLE_CUSTOMER;
        // Password kosong → hash acak tak terpakai; user set via "Lupa Password" (PRD 48.4).
        $data['password'] = Hash::make($data['password'] ?? Str::random(40));
        $data['is_migrated'] = true;

        $user = User::create($data);
        AdminLog::record($request->user(), 'create', 'User', $user->id, null, collect($data)->except('password')->all(), 'Tambah user (inject)');

        return redirect()->route('office.users.index')->with('status', 'User dibuat.');
    }

    /**
     * EPIC-03-22: kirim link reset password manual ke email user yang stuck.
     */
    public function forceResetPassword(Request $request, User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);
        AdminLog::record($request->user(), 'force_reset_password', 'User', $user->id, null, null, 'Kirim link reset password manual');

        return redirect()->route('office.users.index')->with('status', 'Link reset password dikirim ke email user.');
    }

    /**
     * EPIC-03-22: verifikasi email manual untuk user yang tidak bisa akses email.
     */
    public function forceVerifyEmail(Request $request, User $user): RedirectResponse
    {
        $user->forceFill(['email_verified_at' => now()])->save();
        AdminLog::record($request->user(), 'force_verify_email', 'User', $user->id, ['email_verified_at' => null], ['email_verified_at' => (string) $user->email_verified_at], 'Verifikasi email manual');

        return redirect()->route('office.users.index')->with('status', 'Email user diverifikasi.');
    }

    /**
     * PRD 22.6: kirim notifikasi manual ke user terkait.
     */
    public function notify(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'link' => ['nullable', 'string', 'max:255'],
        ]);

        app(UserNotificationService::class)->notify(
            $user,
            'admin_manual',
            $data['title'],
            $data['link'] ?? null,
            $data['message']
        );

        AdminLog::record($request->user(), 'notify', 'User', $user->id, null, $data, 'Kirim notifikasi manual');

        return redirect()->route('office.users.index')->with('status', 'Notifikasi terkirim ke user.');
    }

    /**
     * PRD 22.6/7.10: hard delete akun — permanen, koin ikut terhapus, tidak bisa restore.
     * Ditolak bila user punya order (riwayat finansial tidak boleh lenyap, orders FK cascade).
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        if ($user->isSuperAdmin()) {
            return back()->withErrors(['delete' => 'Akun super_admin tidak dapat dihapus.']);
        }

        if ($user->orders()->exists()) {
            return back()->withErrors(['delete' => 'User memiliki riwayat order dan tidak dapat dihapus.']);
        }

        AdminLog::record($request->user(), 'delete', 'User', $user->id, $user->only(['name', 'email', 'role']), null, 'Hard delete user');
        $user->delete();

        return redirect()->route('office.users.index')->with('status', 'User dihapus permanen.');
    }

    /**
     * PRD 10.6: Forever Delete — HANYA user di spammer list.
     * Konfirmasi keras = email user; data spammers tetap aktif selamanya.
     */
    public function foreverDelete(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if ($user->isSuperAdmin() || $user->is($request->user())) {
            return back()->withErrors(['confirmation' => 'Akun super_admin atau akun Anda sendiri tidak dapat dihapus.']);
        }

        $inSpammerList = Spammer::where('email', $user->email)->exists();

        if (! $inSpammerList) {
            return back()->withErrors(['confirmation' => 'Forever Delete hanya untuk user di spammer list.']);
        }

        if ($data['confirmation'] !== $user->email) {
            return back()->withErrors(['confirmation' => 'Konfirmasi harus persis berupa email user.']);
        }

        if ($user->orders()->exists()) {
            return back()->withErrors(['confirmation' => 'User memiliki riwayat order dan tidak dapat dihapus.']);
        }

        AdminLog::record($request->user(), 'forever_delete', 'User', $user->id, $user->only(['name', 'email', 'role']), null, 'Forever Delete (data spammer tetap)');
        $user->delete();

        return redirect()->route('office.users.index')->with('status', 'Akun dihapus permanen; data spammer tetap aktif.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'visit' => $this->visitSourceFor($user),
            'vouchers' => Voucher::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * PRD 12/22.6: referral_source + landing_page kunjungan pertama user.
     *
     * @return array{source: ?string, landing_page: ?string}
     */
    private function visitSourceFor(User $user): array
    {
        $visit = VisitorTracking::where('user_id', $user->id)->oldest('id')->first();

        return [
            'source' => $visit?->source,
            'landing_page' => $visit?->landing_page,
        ];
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        [$old, $new] = AdminLog::diff($user, ['name', 'email', 'role'], $data);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        AdminLog::record($request->user(), 'update', 'User', $user->id, $old, $new, 'Ubah user');

        return redirect()->route('office.users.index')->with('status', 'User diperbarui.');
    }
}
