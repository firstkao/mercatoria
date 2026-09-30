<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\IdentityRecord;
use App\Models\NameBlacklistEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user(),
            'provinces' => config('wilayah.provinces'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100', 'regex:/^\p{Latin}+(?: \p{Latin}+)+$/u'],
            'province' => ['required', Rule::in(config('wilayah.provinces'))],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'digits:5'],
            'street_address' => ['required', 'string', 'max:500'],
            'whatsapp' => ['required', 'regex:/^628\d{7,11}$/', Rule::unique('users', 'whatsapp')->ignore($user->id)],
        ]);

        // Samakan dengan pendaftaran: nama karakter dan nomor terblokir tidak boleh lolos
        // lewat edit profil. Hanya dicek kalau nilainya benar-benar diubah, supaya user lama
        // yang cuma mengedit alamat tidak ikut terkunci.
        $errors = [];

        if ($validated['full_name'] !== $user->full_name && NameBlacklistEntry::matches($validated['full_name'])) {
            $errors['full_name'] = 'Gunakan nama asli sesuai identitas, bukan nama karakter.';
        }

        if ($validated['whatsapp'] !== $user->whatsapp && IdentityRecord::anyBlocked(null, $validated['whatsapp'])) {
            $errors['whatsapp'] = 'Nomor WhatsApp ini diblokir. Hubungi admin melalui chat untuk membuka blokir.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($user, $validated, $request): void {
            $user->update($validated);
            ActivityLog::record($user, 'update_profile', $request);
        });

        return redirect()->route('account.show')->with('status', 'Profil berhasil diperbarui.');
    }
}
