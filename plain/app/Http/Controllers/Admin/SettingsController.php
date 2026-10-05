<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DisplaySettingsRequest;
use App\Http\Requests\Admin\GeneralSettingsRequest;
use App\Http\Requests\Admin\MarketplaceSettingsRequest;
use App\Http\Requests\Admin\PricingSettingsRequest;
use App\Http\Requests\Admin\SeoSettingsRequest;
use App\Http\Requests\Admin\TierSettingsRequest;
use App\Models\AdminLog;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Setting;
use App\Models\ShippingTier;
use App\Models\SocialMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const PRICING_KEYS = ['exchange_rate', 'cn_id_rate_per_kg', 'btm_jkt_rate_per_kg', 'margin_percent', 'price_rounding'];

    private const GENERAL_KEYS = [
        'contact_email',
        'contact_whatsapp',
        'contact_hours',
        'store_address',
    ];
    
    private const SEO_KEYS = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_image',
    ];

    public function pricing(): View
    {
        return view('admin.settings.pricing', [
            'settings' => $this->values(self::PRICING_KEYS),
            'tiers' => ShippingTier::query()->orderByDesc('code')->get(),
        ]);
    }

    public function updatePricing(PricingSettingsRequest $request): RedirectResponse
    {
        $this->store($request->validated(), self::PRICING_KEYS);
        AdminLog::record('update_pricing', null, $request->validated());

        return redirect()->route('admin.settings.pricing')->with('status', 'Harga & kurs disimpan.');
    }

    public function tiers(): View
    {
        return view('admin.settings.tiers', [
            'tiers' => ShippingTier::query()->withCount('products')->orderByDesc('code')->get(),
        ]);
    }

    public function updateTiers(TierSettingsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->validated('tiers') as $index => $row) {
                $tier = isset($row['id']) ? ShippingTier::findOrFail($row['id']) : new ShippingTier;

                if (! empty($row['delete'])) {
                    if ($tier->exists && Product::where('shipping_tier_id', $tier->id)->exists()) {
                        throw ValidationException::withMessages(["tiers.{$index}.code" => "Tier {$tier->code} masih dipakai produk, jadi tidak bisa dihapus."]);
                    }
                    $tier->exists && $tier->delete();

                    continue;
                }

                $tier->fill([
                    'code' => $row['code'],
                    'fee_yuan' => $row['fee_yuan'],
                    'min_purchase_yuan' => $row['min_purchase_yuan'],
                ])->save();
            }
        });

        AdminLog::record('update_tiers');

        return redirect()->route('admin.settings.tiers')->with('status', 'Tier ongkir disimpan.');
    }

    public function marketplaces(): View
    {
        return view('admin.settings.marketplaces', [
            'marketplaces' => Marketplace::query()->orderBy('id')->get(),
        ]);
    }

    public function updateMarketplaces(MarketplaceSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('marketplaces') as $id => $row) {
            Marketplace::whereKey($id)->update([
                'fp_fee_idr' => $row['fp_fee_idr'],
                'dp_fee_percent' => $row['dp_fee_percent'],
                'is_active' => ! empty($row['is_active']),
            ]);
        }

        AdminLog::record('update_marketplaces', null, $request->validated('marketplaces'));

        return redirect()->route('admin.settings.marketplaces')->with('status', 'Biaya marketplace disimpan.');
    }

    public function display(): View
    {
        return view('admin.settings.display', [
            'settings' => $this->values(['promo_bar_text']),
        ]);
    }

    public function updateDisplay(DisplaySettingsRequest $request): RedirectResponse
    {
        $this->store($request->validated(), ['promo_bar_text']);
        AdminLog::record('update_display', null, $request->validated());

        return redirect()->route('admin.settings.display')->with('status', 'Tampilan toko disimpan.');
    }

    // ============================================================
    // TAMBAHAN BATCH 12: General (kontak & sosmed)
    // ============================================================

    public function general(): View
    {
        return view('admin.settings.general', [
            'settings' => $this->values(self::GENERAL_KEYS),
            // CRUD sosial media dinamis: disatukan di halaman Pengaturan Umum.
            'socialMedias' => SocialMedia::orderBy('sort_order')->get(),
        ]);
    }

    public function updateGeneral(GeneralSettingsRequest $request): RedirectResponse
    {
        $this->store($request->validated(), self::GENERAL_KEYS);

        // Simpan baris sosial media (array dari form). Baris dengan flag `delete`
        // dihapus; baris tanpa `id` dibuat baru; sisanya di-update.
        foreach ($request->validated('socials', []) as $row) {
            if (! empty($row['delete'])) {
                if (! empty($row['id'])) {
                    SocialMedia::destroy($row['id']);
                }

                continue;
            }

            // BUG FIX (ikon sosmed hilang walau data ada): normalisasi is_active
            // SEBELUM rule validasi sempat membuang key-nya. Hidden input "0" +
            // checkbox "1" selalu terkirim berpasangan; kalau karena alasan apa
            // pun key lenyap/gagal validasi, JANGAN diam-diam mematikan baris:
            // pakai status lama baris tsb. sebagai fallback.
            if (array_key_exists('is_active', $row)) {
                $isActive = filter_var(
                    is_string($row['is_active']) ? trim($row['is_active']) : $row['is_active'],
                    FILTER_VALIDATE_BOOLEAN
                );
            } elseif (! empty($row['id'])) {
                $isActive = (bool) optional(SocialMedia::find($row['id']))->is_active;
            } else {
                // Baris baru tanpa info aktif -> default aktif (sama seperti
                // default kolom di migration), bukan mati.
                $isActive = true;
            }

            $data = [
                'name' => $row['name'],
                // Trim URL: baris dengan url spasi/kosong dianggap tidak punya
                // URL dan otomatis disembunyikan layout (konsisten dgn filter).
                'url' => ($u = trim((string) ($row['url'] ?? ''))) === '' ? null : $u,
                'icon_url' => ($iu = trim((string) ($row['icon_url'] ?? ''))) === '' ? null : $iu,
                // Normalisasi icon_key (trim + lower) supaya pencocokan SVG
                // bawaan di partial social-icon tidak gagal karena kapitalisasi.
                'icon_key' => ($ik = strtolower(trim((string) ($row['icon_key'] ?? '')))) === '' ? null : $ik,
                'sort_order' => $row['sort_order'] ?? 0,
                'is_active' => $isActive,
            ];

            if (! empty($row['id'])) {
                $sm = SocialMedia::find($row['id']);
                if ($sm !== null) {
                    $sm->fill($data)->save();
                }
            } else {
                SocialMedia::create($data);
            }
        }

        AdminLog::record('update_general', null, $request->validated());

        return redirect()->route('admin.settings.general')->with('status', 'Pengaturan umum disimpan.');
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, ?string>
     */
    private function values(array $keys): array
    {
        return collect($keys)->mapWithKeys(fn (string $key): array => [$key => Setting::get($key)])->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function store(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            Setting::updateOrCreate(['key' => $key], ['value' => $value === null || $value === '' ? null : (string) $value]);
        }
    }
    
        public function seo(): View
    {
        return view('admin.settings.seo', [
            'settings' => $this->values(self::SEO_KEYS),
        ]);
    }

    public function updateSeo(\App\Http\Requests\Admin\SeoSettingsRequest $request): RedirectResponse
    {
        $this->store($request->validated(), self::SEO_KEYS);
        AdminLog::record('update_seo', null, $request->validated());

        return redirect()->route('admin.settings.seo')->with('status', 'Pengaturan SEO disimpan.');
    }
}