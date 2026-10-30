<?php

namespace App\Http\Controllers;

use App\Models\IndonesiaRegion;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Dropdown berantai alamat (PRD 7.3): provinsi -> kota -> kecamatan -> kode pos.
     * Data publik untuk form register & edit profil (guest boleh akses).
     */
    public function provinces(): JsonResponse
    {
        return response()->json(
            IndonesiaRegion::type(IndonesiaRegion::TYPE_PROVINSI)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'nama' => $r->name])
                ->values()
        );
    }

    public function regencies(int $province): JsonResponse
    {
        return response()->json(
            IndonesiaRegion::type(IndonesiaRegion::TYPE_KOTA)
                ->where('parent_id', $province)
                ->orderBy('name')
                ->get(['id', 'name', 'subtype'])
                ->map(fn ($r) => ['id' => $r->id, 'nama' => $r->name, 'tipe' => $r->subtype])
                ->values()
        );
    }

    public function districts(int $regency): JsonResponse
    {
        return response()->json(
            IndonesiaRegion::type(IndonesiaRegion::TYPE_KECAMATAN)
                ->where('parent_id', $regency)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'nama' => $r->name])
                ->values()
        );
    }

    public function postalCodes(int $district): JsonResponse
    {
        return response()->json(
            IndonesiaRegion::type(IndonesiaRegion::TYPE_KODE_POS)
                ->where('parent_id', $district)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'kode' => $r->name])
                ->values()
        );
    }
}
