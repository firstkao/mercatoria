<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileCompleteController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasCompletedProfile()) {
            return redirect()->route('dashboard');
        }

        return view('profile.complete');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Locked: sekali isi gak bisa diubah
        if ($user->hasCompletedProfile()) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'tgl_lahir' => ['required', 'date', 'after:1900-01-01', 'before_or_equal:today'],
            'konfirmasi' => ['required', 'in:YAKIN'],
        ], [
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.date' => 'Format tanggal tidak valid.',
            'tgl_lahir.after' => 'Tanggal lahir tidak valid.',
            'tgl_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'konfirmasi.in' => 'Ketik YAKIN untuk konfirmasi.',
        ]);

        $user->update(['tgl_lahir' => $data['tgl_lahir']]);

        return redirect()->route('dashboard')->with('status', 'Profil berhasil dilengkapi.');
    }
}
