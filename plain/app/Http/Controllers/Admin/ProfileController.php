<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit', [
            'admin' => Auth::guard('admin')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:admins,email,' . $admin->id],
        ]);

        $admin->update($data);
        AdminLog::record('update_profile', null, ['email' => $admin->email]);

        return back()->with('status', 'Profil diperbarui.');
    }

    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ], [], [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ]);

        $admin = Auth::guard('admin')->user();
        $admin->update(['password' => Hash::make($request->input('password'))]);

        AdminLog::record('update_password', null, ['email' => $admin->email]);

        return back()->with('status', 'Kata sandi diperbarui.');
    }
}