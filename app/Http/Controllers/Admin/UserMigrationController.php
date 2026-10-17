<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserMigrationController extends Controller
{
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

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
            'no_hp' => trim((string) $request->input('no_hp')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'regex:/^62[0-9]{8,13}$/', 'unique:users,no_hp'],
            'provinsi' => ['required', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'alamat_lengkap' => ['required', 'string', 'max:2000'],
            'kode_pos' => ['required', 'string', 'max:10'],
        ]);

        $user = User::create([
            ...$validated,
            'password' => Str::random(64),
            'role' => 'customer',
            'is_migrated' => true,
        ]);

        return redirect()->to("/office/orders/create?user_id={$user->id}")
            ->with('success', 'Customer lama berhasil dimigrasikan. Lanjutkan dengan memasukkan order lamanya.');
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'customer', 404);

        return view('admin.users.show', compact('user'));
    }

    public function forceResetPassword(User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer', 404);

        $log = AdminLog::record(
            'force_password_reset',
            User::class,
            $user->id,
            new: ['status' => 'requested'],
            description: 'Admin mengirim link reset password untuk customer.',
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
}
