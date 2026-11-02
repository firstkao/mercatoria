<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinTransaction;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserGift;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Notifications\UserGiftNotification;
use App\Services\User\CoinWalletService;
use App\Services\User\UserNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * PRD 27 / Design 29.2: Rank Pelanggan (hanya role Customer, bukan Crush) + Kirim Gift.
 */
class CustomerRankController extends Controller
{
    private const SORTS = ['total_belanja', 'total_orders', 'aov', 'name'];

    private const NON_SALES_STATUSES = ['menunggu_pembayaran', 'pembayaran_gagal', 'hangus', 'hangus_permanen', 'dana_dikembalikan'];

    public function index(Request $request): View
    {
        $rows = $this->query($request)->paginate(25)->withQueryString();

        return view('admin.customers.rank', [
            'rows' => $rows,
            'vouchers' => Voucher::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'sort' => $this->sort($request),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->query($request);

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'Email', 'State', 'Tier', 'Total Order', 'Total Belanja', 'AOV']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $this->csvSafe($row->name), $this->csvSafe($row->email), $row->current_state, $row->tier,
                        $row->total_orders, $row->total_belanja, round($row->aov),
                    ]);
                }
            });
            fclose($out);
        }, 'rank-pelanggan-'.now()->format('Y-m-d-His').'.csv');
    }

    public function gift(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_CUSTOMER, 404);

        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', [UserGift::TYPE_COIN, UserGift::TYPE_VOUCHER, UserGift::TYPE_GIFT_BOX])],
            'coin_amount' => ['required_if:type,koin', 'nullable', 'integer', 'min:1', 'max:100000000'],
            'voucher_id' => ['required_if:type,voucher', 'nullable', 'integer', 'exists:vouchers,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $gift = DB::transaction(function () use ($data, $user, $request) {
            $gift = UserGift::create([
                'user_id' => $user->id,
                'admin_id' => $request->user()->id,
                'type' => $data['type'],
                'coin_amount' => $data['type'] === UserGift::TYPE_COIN ? $data['coin_amount'] : null,
                'voucher_id' => $data['type'] === UserGift::TYPE_VOUCHER ? $data['voucher_id'] : null,
                'note' => $data['note'] ?? null,
            ]);

            if ($data['type'] === UserGift::TYPE_COIN) {
                app(CoinWalletService::class)->credit($user, (int) $data['coin_amount'], null, CoinTransaction::TYPE_ADMIN_GIFT, $data['note'] ?? 'Gift dari admin');
            } elseif ($data['type'] === UserGift::TYPE_VOUCHER) {
                $voucher = Voucher::findOrFail($data['voucher_id']);
                UserVoucher::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'status' => UserVoucher::STATUS_ACTIVE,
                    'expires_at' => $voucher->valid_days ? now()->addDays($voucher->valid_days) : null,
                ]);
            }

            AdminLog::record($request->user(), 'gift', 'User', $user->id, null, $gift->only(['type', 'coin_amount', 'voucher_id', 'note']), 'Kirim gift '.$data['type']);

            return $gift;
        });

        app(UserNotificationService::class)->notify($user, 'admin_gift', 'Kamu mendapat hadiah dari admin', '/profile/koin', $gift->note);
        $user->notify(new UserGiftNotification);

        return back()->with('status', 'Gift terkirim ke '.$user->name.'.');
    }

    private function sort(Request $request): string
    {
        return in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'total_belanja';
    }

    private function query(Request $request): Builder
    {
        $excluded = "'".implode("','", self::NON_SALES_STATUSES)."'";
        $sort = $this->sort($request);
        $direction = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        return User::query()
            ->where('users.role', User::ROLE_CUSTOMER)
            ->join('customers', 'customers.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'customers.current_state', 'customers.tier', 'customers.total_belanja')
            ->selectRaw("(select count(*) from orders where orders.user_id = users.id and orders.status not in ({$excluded})) as total_orders")
            ->selectRaw("customers.total_belanja / nullif((select count(*) from orders where orders.user_id = users.id and orders.status not in ({$excluded})), 0) as aov")
            ->when($request->filled('state'), fn ($q) => $q->where('customers.current_state', $request->query('state')))
            ->when($request->filled('tier'), fn ($q) => $q->where('customers.tier', $request->query('tier')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('users.name', 'like', '%'.$request->query('q').'%')
                ->orWhere('users.email', 'like', '%'.$request->query('q').'%')))
            ->orderBy($sort === 'name' ? 'users.name' : DB::raw($sort), $direction)
            ->orderBy('users.id');
    }

    private function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
