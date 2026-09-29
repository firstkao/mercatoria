<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        DB::transaction(function () use ($user, $validated, $request): void {
            $user->update($validated);
            ActivityLog::record($user, 'update_profile', $request);
        });

        return redirect()->route('account.show')->with('status', 'Profil berhasil diperbarui.');
    }
}