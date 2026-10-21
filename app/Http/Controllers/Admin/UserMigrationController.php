<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserState;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\Customer;
use App\Models\Spammer;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserMigrationController extends Controller
{
    public const PROVINCES = ['Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Bengkulu', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Lampung', 'Banten', 'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara', 'Sulawesi Utara', 'Gorontalo', 'Sulawesi Tengah', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->where('role', 'customer')
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%"),
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pageUsers = $users->getCollection();
        $emails = $pageUsers->pluck('email')->filter()->all();
        $phoneNumbers = $pageUsers->pluck('no_hp')->filter()->all();
        $blockedSpammers = collect();
        if ($emails || $phoneNumbers) {
            $blockedSpammers = Spammer::query()
                ->where('status', 'blocked')
                ->where(function ($query) use ($emails, $phoneNumbers): void {
                    if ($emails) {
                        $query->whereIn('email', $emails);
                    }
                    if ($phoneNumbers) {
                        $query->{$emails ? 'orWhereIn' : 'whereIn'}('no_hp', $phoneNumbers);
                    }
                })
                ->get(['email', 'no_hp']);
        }
        $blockedEmails = $blockedSpammers->pluck('email')->filter()->all();
        $blockedPhoneNumbers = $blockedSpammers->pluck('no_hp')->filter()->all();
        $pageUsers->each(function (User $user) use ($blockedEmails, $blockedPhoneNumbers): void {
            $user->setAttribute(
                'blocked_spammer',
                in_array($user->email, $blockedEmails, true) || in_array($user->no_hp, $blockedPhoneNumbers, true),
            );
        });

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['provinces' => self::PROVINCES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
            'no_hp' => $this->normalizePhone((string) $request->input('no_hp')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'regex:/^62[0-9]{8,13}$/', 'unique:users,no_hp'],
            'provinsi' => ['required', 'string', Rule::in(self::PROVINCES)],
            'kota' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string', 'max:2000'],
            'kode_pos' => ['required', 'string', 'max:10'],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::create([
                ...$validated,
                'password' => Str::random(64),
                'role' => 'customer',
                'is_migrated' => true,
            ]);
            Customer::create([
                'user_id' => $user->id,
                'current_state' => UserState::MVP->value,
                'state_updated_at' => now(),
            ]);

            return $user;
        });

        return redirect()->to("/office/orders/create?user_id={$user->id}")
            ->with('success', 'Customer lama berhasil dimigrasikan. Lanjutkan dengan memasukkan order lamanya.');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($user->role === 'customer', 404);
        $blockedSpammer = Spammer::query()
            ->where('status', 'blocked')
            ->where(fn ($query) => $query->where('email', $user->email)->orWhere('no_hp', $user->no_hp))
            ->exists();

        $vouchers = Voucher::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->orderBy('code')
            ->get();

        $tab = in_array($request->query('tab'), ['orders', 'koin', 'log'], true) ? $request->query('tab') : 'overview';
        $tabData = match ($tab) {
            'orders' => ['orders' => $user->orders()->latest()->limit(20)->get()],
            'koin' => [
                'coinBalance' => (int) ($user->coin?->balance ?? 0),
                'coinTransactions' => $user->coinTransactions()->latest('id')->limit(20)->get(),
            ],
            'log' => ['logs' => ActivityLog::query()->where('user_id', $user->id)->latest('id')->limit(20)->get()],
            default => [],
        };

        return view('admin.users.show', ['user' => $user, 'blockedSpammer' => $blockedSpammer, 'vouchers' => $vouchers, 'tab' => $tab] + $tabData);
    }

    public function forceResetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);
        $note = $request->validate(['note' => ['nullable', 'string', 'max:500']])['note'] ?? null;

        $log = AdminLog::record(
            'force_password_reset',
            User::class,
            $user->id,
            new: ['status' => 'requested'],
            description: 'Admin mengirim link reset password untuk customer.'.($note ? ' Catatan: '.$note : ''),
        );
        $status = Password::sendResetLink(['email' => $user->email]);
        $log->update(['new_value' => ['status' => $status]]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['password_reset' => __($status)]);
        }

        return back()->with('success', 'Link reset password berhasil dikirim ke email customer.');
    }

    public function forceVerifyEmail(User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $previouslyVerified = $user->hasVerifiedEmail();

        if (! $previouslyVerified) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        AdminLog::record(
            'force_verify_email',
            User::class,
            $user->id,
            old: ['email_verified_at' => $previouslyVerified ? $user->email_verified_at?->toISOString() : null],
            new: ['email_verified_at' => $user->fresh()->email_verified_at?->toISOString()],
            description: 'Admin memverifikasi email customer secara manual.',
        );

        return back()->with('success', 'Email customer berhasil diverifikasi.');
    }

    public function foreverDelete(User $user): RedirectResponse
    {
        abort_unless(
            $user->role === 'customer' && Spammer::query()
                ->where('status', 'blocked')
                ->where(fn ($query) => $query->where('email', $user->email)
                    ->orWhere('no_hp', $user->no_hp))
                ->exists(),
            404,
        );

        DB::transaction(function () use ($user): void {
            $user->delete();

            AdminLog::record(
                'forever_delete_spammer_user',
                User::class,
                $user->id,
                old: ['email' => $user->email, 'no_hp' => $user->no_hp],
                description: 'Admin menghapus akun user yang cocok dengan daftar spammer; identitas spammer tetap tersimpan.',
            );
        });

        return redirect()->route('office.users.index')
            ->with('success', 'Akun spammer berhasil dihapus permanen; identitas blokir tetap tersimpan.');
    }
}
