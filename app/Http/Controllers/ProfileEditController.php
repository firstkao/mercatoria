<?php

namespace App\Http\Controllers;

use App\Models\Spammer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileEditController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $phone = preg_replace('/\D/', '', (string) $request->input('no_hp')) ?? '';
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'no_hp' => $phone,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => ['required', 'string', 'regex:/^628[1-9][0-9]{6,11}$/', Rule::unique('users', 'no_hp')->ignore($user->id)],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kode_pos' => ['required', 'string', 'max:10'],
            'alamat_lengkap' => ['required', 'string', 'min:10', 'max:500'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'no_hp.regex' => 'Masukkan nomor WhatsApp aktif, contoh 08123456789.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
        ]);

        $blockedIdentity = Spammer::query()
            ->where('status', 'blocked')
            ->where(function ($query) use ($data): void {
                $query->where('email', $data['email'])->orWhere('no_hp', $data['no_hp']);
            })
            ->exists();
        if ($blockedIdentity) {
            return back()->withInput()->withErrors(['no_hp' => 'Email atau nomor HP ini tidak dapat digunakan.']);
        }

        $emailChanged = $user->email !== $data['email'];
        $user->fill(collect($data)->only([
            'name', 'email', 'no_hp', 'provinsi', 'kota', 'kecamatan', 'kode_pos', 'alamat_lengkap',
        ])->all());
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }
        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('profile.edit')->with('success', $emailChanged
            ? 'Profil tersimpan. Verifikasi email baru untuk melanjutkan.'
            : 'Profil berhasil diperbarui.');
    }
}
