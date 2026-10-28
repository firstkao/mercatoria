# AMANDEMEN /doc — Status TERAPLIKAN

**Status: [SUDAH DITERAPKAN ke dokumen sumber + Tutorial VPS]**  
**Tanggal terapan:** sinkron setelah audit jejak & revisi struktur.

Dokumen yang sudah diperbarui:
- `PRD Mercatoria v4.9` (dari v4.8)
- `Struktur Folder & Paket Mercatoria v1.4` (dari v1.3)
- `Skema Database Laravel Mercatoria v1.7` (dari v1.6)
- `Design Spec Mercatoria v3.3` (dari v3.2)
- `Backlog Mercatoria v1.8` (dari v1.7)
- `Tutorial VPS Mercatoria v1.2` (mengikuti sumber)

---


## 1. PHP & Composer — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]**

| Bagian doc | Nilai lama (doc) | Nilai baru | Alasan |
|---|---|---|---|
| Struktur Folder v1.3 §B0 · PRD 31.2 / 37.1 | PHP 8.5 runtime; `composer` `php: ^8.3` | **`php: ^8.5`** (runtime tetap 8.5) | Constraint composer disamakan runtime |
| Struktur Folder B10 · PRD 37.1 | `barryvdh/laravel-dompdf:^3.1` | **`^3.0`** (terpasang) | Kompatibel Laravel 13; jangan churn versi |
| Struktur Folder B10 · Backlog package matrix | `livewire/livewire:^3.6` | **`^3.5`** (terpasang) | Idem |
| Struktur Folder B10 | `spatie/laravel-activitylog:^4.9` | **`^4.8`** (terpasang) | Idem |
| Struktur Folder B10 | `spatie/laravel-honeypot:^4.5` | **`^4.4`** (terpasang) | Idem |
| Struktur Folder B10 | `stevebauman/location:^7.4` | **`^7.3`** (terpasang) | Idem |
| Struktur Folder B10 · PRD 31.9 | `maatwebsite/excel:^3.1.68\|^4.0` | **`^3.1`** (terpasang) | Idem; fungsi export tetap |
| Struktur Folder B10 | `laravel/tinker:^2.10` | **`^3.0`** (terpasang) | Ikut skeleton L13 |
| Struktur Folder B10 · PRD 31.11 | `laravel/pint:^1.20` · `phpunit:^11.5\|^12` | **`pint:^1.18`** · **`phpunit:^12.5`** (terpasang) | DoD tetap pakai Pint + PHPUnit |
| Struktur Folder B10 | `larastan/larastan:^3.0` | **tetap `^3.0`** | Sudah selaras |
| Struktur Folder B10 · PRD 31.4 / 37.1 | `spatie/laravel-permission:^6.10` | **DIHAPUS dari daftar wajib** | RBAC dipenuhi `users.role` + `EnsureAdmin` (keputusan 1a) |
| Struktur Folder B10 · PRD 37.1 | `spatie/laravel-backup:^9.2` | **DIHAPUS dari daftar wajib** | Backup operasional/hosting; bukan alur belanja (keputusan 1a) |
| Struktur Folder B10 · PRD 31.10 / 37.1 | `spatie/laravel-sluggable:^3.7` | **DIHAPUS dari daftar wajib** | Slug dibuat di controller/model (keputusan 1a) |
| PRD 8.1 / 37.1 · Backlog | `ryangjchandler/laravel-cloudflare-turnstile:^1.1` | **Custom `VerifyTurnstile` + `TurnstileService`** | Doc mengizinkan custom rule `Http::asForm()` |
| Struktur Folder B10 | `guzzlehttp/guzzle:^7.9` | **tidak eksplisit di require** | Sudah dependensi Laravel HTTP |
| Struktur Folder B10 · PRD 31.8 | `intervention/image:^3.11` | **wajib ada di `require`** (kode `ImageManager`); versi ikut lock | Fungsi watermark wajib |
| Backlog EPIC-01-04 | **15 paket Composer produksi** | **jumlah ikut daftar runtime** (setelah hapus heroicons, permission, backup, sluggable; tanpa turnstile pkg) | Hitungan paket disesuaikan |

### Fungsional yang TIDAK berubah
RBAC (admin vs customer), slug produk/halaman, activity log, honeypot, turnstile, watermark, export Excel/PDF, Geo-IP share, sitemap — **tetap 100%**.

---

## 2. Frontend / NPM — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]**

| Bagian doc | Nilai lama (doc) | Nilai baru | Alasan |
|---|---|---|---|
| PRD 31.7 / 37.1 · Struktur Folder B0/B11 | Tailwind CSS **3.4** + Vite **5** | **Tailwind CSS ^4** + **Vite ^6** | Runtime frontend aktual (keputusan user) |
| Struktur Folder B11 | `laravel-vite-plugin:^1.0.5` | **`^1.3`** | Ikut Vite 6 |
| Struktur Folder B11 | `alpinejs:^3.14` | **`^3.17`** (terpasang) | Kompatibel |
| Struktur Folder B11 | `config Tailwind klasik` klasik | **konfigurasi Tailwind 4 via `@tailwindcss/vite`** | Konsekuensi Tailwind 4 |
| Struktur Folder B11 | (tidak disebut) `supabase` | **HAPUS dari package.json** | Tidak ada di doc, tidak dipakai (keputusan 1b) |
| Struktur Folder B11 · PRD 37.1 | `grapesjs@^0.21.10` + `grapesjs-preset-webpage` | **TETAP diminta — TAMBAH ke package.json** | Preorder Builder wajib (keputusan 1c) |
| Struktur Folder B11 | 17 paket NPM | **17 ± GrapesJS** (setelah hapus supabase, tambah 2 grapesjs) | Sinkron daftar |

---

## 3. Ikon — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** (Keputusan revisi total)

| Bagian doc | Nilai lama (doc) | Nilai baru | Alasan |
|---|---|---|---|
| PRD 30.2 · Design Spec 2.6 · Skema DB §24 · Backlog DoD | **vendor ikon lama SVG**, 1 keluarga stroke, `folder SVG icons lamaicons/`, **wajib atribusi / halaman kredit** | **Bootstrap Icons (MIT)**, gaya **filled/solid**, `fill="currentColor"` | Lisensi MIT; tanpa atribusi; satu gaya |
| PRD 30.2 · Design Spec 2.6 | File SVG per ikon di `folder SVG icons lamaicons/` | **Blade component satu ikon satu file** di `resources/views/components/icons/` | Struktur component per-ikon |
| PRD 30.2 · Design Spec 2.6 | `<x-icon name="...">` baca file SVG | **`<x-icon>` → include komponen Bootstrap Icons** | Konsisten |
| Design Spec 32.9 | `fire.svg`, `fire-double.svg`, `handshake.svg`, `lock.svg` (file vendor ikon lama) | **komponen `fire`, `fire-double`, `handshake`, `lock`** (Bootstrap Icons filled) | Nama ikon = Design Spec; isi baru |
| Design Spec 2.6 · PRD 30.2 | Logo sosmed di `public/images/icons/` (SVG brand) | **komponen Bootstrap Icons `instagram`, `facebook`, `x`, `threads`, `whatsapp`** | Satu sistem ikon |
| Semua rujukan vendor ikon lama + atribusi | wajib | **tidak berlaku** (MIT) | Keputusan 2: vendor ikon lama batal |
| Folder `folder SVG icons lama` | ada | **dihapus** setelah 0 referensi | Migrasi tuntas |

---

## 4. Struktur folder — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** / validasi

| Bagian doc | Nilai lama | Nilai baru | Alasan |
|---|---|---|---|
| Struktur Folder B3 `Http/Requests/` | FormRequest untuk login/register/dll | **WAJIB** — validasi Register/Login/Checkout/upload/etc pindah ke FormRequest | Keputusan 1f; service + aturan tetap |
| Register: Buyer Protection (PRD 11) | cek di controller sebelum validate | **`RegisterRequest::withValidator`** + flash `buyer_protection` | Satu pintu validasi FormRequest; fungsi PRD 11 tetap |
| Struktur Folder B3 `Jobs/Mail/Notifications/Policies` | wajib ada | **WAJIB** — sudah dipenuhi; jalur email/notif tetap `ScheduledEmailService` / `UserNotificationService` | Fungsi service dipertahankan |

---

## 5. Perhitungan uang — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** (TIDAK di-amendemen — verifikasi)

| Aturan | Sumber | Status kode |
|---|---|---|
| `rate_rmb_idr` default **3.000** | PRD v4.7 · Skema DB v1.5 | **Sesuai** (seeder + fallback) |
| Profit **11%** | PRD 20.5 | **Sesuai** |
| **CEILING 5.000 sekali di harga item/varian** | PRD 17.6 / 20.5 | **Sesuai** (`calculateItemShipping`) |
| **Cart & Checkout tanpa CEILING** | PRD 17.6 / 20.6 | **Sesuai** (`calculateCheckoutTotal`) |
| Fee QRIS 0,03% bila total ≥ 500.000 | PRD 20.6 | **Sesuai** |
| DP 50% · Shopee +25% · Toco +0 | PRD 20.6 | **Sesuai** |

---

## 6. Keputusan — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** 1a–1f (ringkas)

| ID | Keputusan |
|---|---|
| 1a | `permission` / `backup` / `sluggable` → **hapus + amandemen** |
| 1b | `supabase` → **hapus + amandemen** |
| 1c | `grapesjs` + preset → **tambah + amandemen** |
| 1d | CEILING → **sesuai doc** (hanya di harga item) |
| 1e | Rate 3000 → **sesuai doc** |
| 1f | Register → **FormRequest** (buyer protection di `withValidator`) — **[DITERAPKAN di kode]** |

---

## 7. Temuan #20 — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** — Struktur folder (Events / Listeners / Livewire / Exceptions / Traits / Helpers)

Prinsip: **fungsional doc tetap**; jalur implementasi disepakati = yang ada di repo. Jangan bangun komponen tambahan.

| Kategori | Doc lama mensyaratkan | Implementasi memakai jalur Y | Standar baru (disepakati) |
|---|---|---|---|
| **Events** | 13 event (OrderCreated, OrderStatusChanged, PaymentUploaded, …) | Satu event **`App\Events\OrderStatusChanged`** + side-effect langsung di Service (`OrderPaymentService`, `OrderStatusService`, `RefundService`) | **1 event domain utama** (`OrderStatusChanged`). Event lain tidak diperlukan; panggil service/listener dari service. |
| **Listeners** | 10 listener (SendOrderCreatedEmail, CreateSnapshotOnCheckout, GiveCoinOnOrderCompleted, UpdateUserStateOnOrderCompleted, NotifyAdminOnPaymentUploaded, …) | **4 listener**: `SuppressCustomerEmailWhenPaused`, `SyncTierOnOrderStatusChanged`, `LockSplitSlotsOnPaymentUploaded`, `ReleaseSplitSlots` — email/snapshot/koin/admin-notif lewat Service | **4 listener** tersebut. Proses email/snapshot/koin/notif tetap di `ScheduledEmailService`, `OrderItemSnapshotService`, `CoinWalletService`, `UserNotificationService`. |
| **Livewire** | 10 komponen (CartItem, ProductVariantSelector, NotificationBell, SearchBox, ProductTable, OrderTable, UserTable, + SplitBox*) | **3 Livewire**: `SplitBoxPicker`, `TierProgress`, `SplitBoxManager`. Cart/search/tabel admin = **Blade + Alpine + Controller** | **3 Livewire** hanya untuk interaksi real-time (split box, tier). Sisanya Blade/Alpine/Controller. |
| **Exceptions** | 5 (Handler, InsufficientStockException, InvalidOrderTransitionException, SnapshotCreationFailedException, SlotUnavailableException) | **`SlotUnavailableException`** + validasi Laravel `ValidationException` / `RuntimeException` bawaan | **1 custom exception** (Split Box). Error lain pakai exception framework. |
| **Traits** | 4 (LogsActivity, LogsAdminActivity, HasReferralCode, HasFullName) | **`LogsActivity`**, **`LogsAdminActivity`** (sebagian via `AdminLog::record` langsung). Referral/Name = method model/controller | **2 traits** logging. Helper referral/nama tidak perlu trait. |
| **Helpers** | `helpers.php`, `class IP helper.php`, `class nomor order.php` | **`app/Helpers/helpers.php`** (autoload): `setting()`, `get_real_ip()`, `ip_in_cidr()`, `generate_order_number()`, `format_*` | **1 file helpers** gabungan. Class helper terpisah tidak dipakai. |
| **Actions / Repositories / Support / Casts / Observers / Rules / Exports / ViewComponents** | Lihat §7.1 mapping lengkap per file | **Tidak ada folder tersebut** — logika di Service / Controller / FormRequest / helpers / model cast | **Tidak dipakai.** Standar = Service + FormRequest + Blade anonymous + cast bawaan Eloquent. |
| **routes/api.php** | polling bell | Route web (`/profile/notifications/unread-count`) | **Route web** untuk polling; tanpa `api.php`. |
| **config Tailwind klasik** | Config Tailwind klasik | **Tailwind 4** via `@tailwindcss/vite` + `vite.config.js` | Tanpa `config Tailwind klasik`. |

### 7.1 Mapping lengkap item Struktur Folder yang TIDAK ada di repo

Format: **doc lama → implementasi → standar baru → alasan**.

#### folder Action (opsional di doc)

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `app/folder ActionOrder/CreateOrderAction.php` | `App\Services\Cart\CheckoutService::placeOrder()` | Service method | Alur checkout + snapshot ada di service |
| `app/folder ActionOrder/CancelOrderAction.php` | `App\Services\Order\OrderStatusService::changeStatus()` + `SplitBoxService::cancelUnsatisfiablePendingOrders()` | Service | Pembatalan = transisi status + hook slot |
| `app/folder ActionOrder/CompleteOrderAction.php` | `OrderStatusService::changeStatus(..., SELESAI)` | Service | Satu pintu status |
| `app/folder ActionProduct/CreateProductAction.php` | `Admin\ProductController::store()` + `attributes()` | Controller + FormRequest | Validasi file/gambar sudah di controller |
| `app/folder ActionProduct/HardDeleteProductAction.php` | `App\Services\Product\ProductHardDeleteService` | Service | Hard delete + snapshot integrity |
| `app/folder ActionUser/RegisterUserAction.php` | `Auth\RegisterController::store()` + `RegisterRequest` | Controller + FormRequest | Buyer protection/spammer di register |

**Standar baru:** folder `folder Action` **tidak dipakai** (doc sendiri: opsional). **Alasan:** satu Service per domain cukup untuk tim kecil; hindari lapisan ketiga.

#### Casts/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `app/Casts/cast Money.php` | Cast `decimal:2` di model (`Order`, `Product`, `UserTierService`, dst.) | Cast bawaan Eloquent | Uang disimpan decimal; format Rupiah = `format_rupiah()` |

#### Exports/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Exports/OrdersExport.php` | `Admin\ReportsController` / stream CSV | Controller + `maatwebsite/excel` | Export laporan & rank inline |
| `Exports/UsersExport.php` | `ReportsController::exportCustomers()` | Idem | |
| `Exports/ProductsExport.php` | (belum ada; bisa ditambah di ReportsController bila perlu) | Idem | Belum diminta UI |
| `Exports/SalesReportExport.php` | `ReportsController::index` + export | Idem | |

**Standar baru:** tanpa `app/Exports/`; export di controller. **Alasan:** hanya 2–3 laporan.

#### Helpers/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Helpers/class IP helper.php` | `get_real_ip()` + `ip_in_cidr()` di `helpers.php` | Function di `app/Helpers/helpers.php` | Autoload files; kecil |
| `Helpers/class nomor order.php` | `generate_order_number()` di `helpers.php` | Idem | PRD 17.8 `MER-{5}-{4}` |

#### Observers/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Observers/ProductObserver.php` | `ProductHardDeleteService` / `ProductController` | Service explicit | Side-effect eksplisit, mudah diuji |
| `Observers/OrderObserver.php` | `OrderStatusService` + `SyncTierOnOrderStatusChanged` | Service + Listener | |
| `Observers/UserObserver.php` | `RegisterController` / `UserMigrationController` | Controller + Service | |

**Standar baru:** tanpa Observer. **Alasan:** hindari side-effect tersembunyi.

#### Rules/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Rules/PhoneNumberRule.php` | `regex` + `prepareForValidation` di `RegisterRequest` | FormRequest | |
| `Rules/UniqueVoucherCodeRule.php` | `Rule::unique` di `VoucherController` | FormRequest / Rule inline | |

#### Repositories/ (opsional)

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Repositories/ProductRepository.php` | `Product` model + `ProductSearchService` | Model + Service | Doc: opsional |
| `Repositories/OrderRepository.php` | `Order` model + `OrderStatusService` | Model + Service | |

#### Support/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Support/Money.php` | `format_rupiah()` di `helpers.php` | Function | |
| `Support/Timezone.php` | `config/app.php` timezone `Asia/Jakarta` + `date_default_timezone` | Config | |

#### View/Components/

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `View/Components/BadgeRole.php` | `resources/views/components/badge-state.blade.php` | Blade anonymous | |
| `View/Components/BadgeTier.php` | idem (badge tier di `badge-state`) | Idem | |
| `View/Components/Button.php` | `components/button.blade.php` | Idem | |
| `View/Components/Alert.php` | `components/alert.blade.php` | Idem | |
| `View/Components/Modal.php` | `components/modal.blade.php` | Idem | |

**Standar baru:** **Blade anonymous** di `resources/views/components/` (+ `icons/`). **Alasan:** tidak perlu class PHP per komponen statis.

#### Services (nama doc → nama repo)

| Doc lama | Implementasi | Standar baru | Alasan |
|---|---|---|---|
| `Services/Order/OrderNumberService.php` | `generate_order_number()` | Helpers | |
| `Services/Order/OrderHangusService.php` | `Console/Commands/ExpireUnclaimedOrders` + `OrderStatusService` | Command + Service | |
| `Services/Product/ProductPriceService.php` | `ProductPriceFormatter` + `ShippingCalculatorService` | Service | |
| `Services/Product/ProductCategoryService.php` | `Admin\CatalogTaxonomyController` | Controller | |
| `Services/Product/ImageWatermarkService.php` | `app/Services/Image/ImageWatermarkService.php` | **Pindah namespace `Services\Image`** | Ikuti repo |
| `Services/Payment/PaymentVerificationService.php` | `Services/Order/OrderPaymentService` | Service Order | |
| `Services/Payment/PriceCalculatorService.php` | `ShippingCalculatorService::calculateCheckoutTotal()` | Service Order | AMANDEMEN sebelumnya |
| `Services/Payment/RefundService.php` | `Services/Order/RefundService` | Service Order | |
| `Services/User/UserStateService.php` | `Console/Commands/UpdateCustomerStates` | Command | |
| `Services/User/SpammerDetectionService.php` | `RegisterController` + `EnforceIpBlacklist` | Controller + Middleware | |
| `Services/Coin/CoinService.php` | `Services/User/CoinWalletService` | Service User | |
| `Services/Coin/ReferralService.php` | `Services/User/ReferralRewardService` | Service User | |
| `Services/Notification/AdminNotificationService.php` | `AdminNotification::create(...)` di service/controller | Model create | |
| `Services/Bot/BotCrawlerDetectorService.php` | `Services/Security/CrawlerBotService` | Service Security | |
| `Services/Seo/SeoMetaService.php` | `SeoSettingsController` + layout meta Blade | Controller + Blade | |
| `Services/Geo/GeoIpService.php` | `Services/Security/GeoIpCountryResolver` | Service Security | |

#### Console command names

| Doc lama | Implementasi |
|---|---|
| `PurgeLogs.php` | `PurgeRetainedLogs` (`log:purge`) |
| `PurgeNotifications.php` | `PurgeOldNotifications` (`notification:purge`) |
| `UpdateUserState.php` | `UpdateCustomerStates` (`user:update-state`) |
| `PurgeCrush.php` | `PurgeExpiredCrushes` (`user:purge-crush`) |
| `CancelExpiredOrders.php` | `ExpireUnclaimedOrders` (`orders:hangus-expired`) |
| `SendCartReminders.php` | `SendCartReminders` ✓ |
| `NotifyCoinExpiry.php` | `NotifyExpiringCoins` (`coin:expiry-notify`) |
| `BirthdayBonus.php` | `SendBirthdayRewards` (`user:birthday-bonus`) |
| `RecheckSpammerGrace.php` | `ReblockExpiredSpammers` (`spammers:recheck-grace`) |
| `UpdateCustomerTier.php` | `UpdateCustomerTiers` (`customer:update-tier`) |
| `CheckSplitBoxBatches.php` | `CheckSplitBoxBatches` ✓ |
| (+ doc tidak punya) | `DeploymentCheck`, `GenerateSitemap`, `SendPickupDeadlineReminders` |

#### Root / frontend

| Doc lama | Implementasi | Standar baru |
|---|---|---|
| `config Tailwind klasik` | `vite.config.js` + `@tailwindcss/vite` | TW4 |
| `routes/api.php` | `routes/web.php` polling | Tanpa api.php |
| `lang/` | opsional / belum dipakai | Bahasa default app |

---

## 8. Temuan #22 — **[SUDAH DITERAPKAN ke doc sumber + Tutorial]** — `customers.tier`

| Bagian doc | Nilai lama | Nilai baru | Alasan |
|---|---|---|---|
| Skema DB v1.6 §21.10 | `string('tier', 16)->nullable()` | **`string('tier', 16)->default('silver')` NOT NULL** | Ikuti implementasi `2026_10_14_000003_create_customers_table.php`; tier selalu terisi. |

---

*Akhir amandemen — perbarui file ini bila ada keputusan berikutnya.*
