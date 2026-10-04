<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function index()
    {
        $socialMedias = SocialMedia::orderBy('sort_order')->get();

        return view('admin.settings.social-media', compact('socialMedias'));
    }

    private function validateItem(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'nullable|url|max:2048',
            'icon_url' => 'nullable|url|max:2048',
            'icon_key' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['icon_key'] = $data['icon_key'] ?? null;
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    public function store(Request $request)
    {
        SocialMedia::create($this->validateItem($request));

        return redirect()->route('admin.settings.social-media')
            ->with('success', 'Sosial media berhasil ditambahkan.');
    }

    public function update(Request $request, SocialMedia $socialMedium)
    {
        $socialMedium->update($this->validateItem($request));

        return redirect()->route('admin.settings.social-media')
            ->with('success', 'Sosial media "'.$socialMedium->name.'" berhasil diupdate.');
    }

    public function destroy(SocialMedia $socialMedium)
    {
        $socialMedium->delete();

        return redirect()->route('admin.settings.social-media')
            ->with('success', 'Sosial media berhasil dihapus.');
    }
}
