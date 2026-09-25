<?php

namespace App\Http\Controllers\Admin;

use App\Actions\AnonymizeCustomer;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\IdentityRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('peran'));
        $search = trim((string) $request->query('q'));
        $digits = preg_replace('/\D+/', '', $search);

        $users = $this->withTracking(User::query())
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->when(strlen((string) $digits) >= 4, fn ($q) => $q->orWhere('whatsapp', 'like', '%'.ltrim((string) $digits, '0').'%'))))
            ->latest('registered_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'counts' => [
                'semua' => User::count(),
                'spammer' => User::where('role', UserRole::Spammer)->count(),
                'customer' => User::where('role', UserRole::Customer)->count(),
            ],
            'filters' => ['q' => $search, 'peran' => $role?->value],
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }

    public function show(User $user): View
    {
        $user = $this->withTracking(User::query())->findOrFail($user->id);

        return view('admin.users.show', [
            'user' => $user,
            'activities' => $user->activityLogs()->latest('id')->limit(20)->get(),
            'identities' => IdentityRecord::query()
                ->where(fn ($query) => $query
                    ->where(fn ($q) => $q->where('kind', IdentityRecord::KIND_EMAIL)->where('value', $user->email))
                    ->orWhere(fn ($q) => $q->where('kind', IdentityRecord::KIND_WHATSAPP)->where('value', $user->whatsapp)))
                ->get(),
            'viewQuota' => Setting::integer('view_quota', 10),
            'limit' => Setting::integer('registration_limit', 3),
        ]);
    }

    public function resetQuota(User $user): RedirectResponse
    {
        $before = $user->view_quota_used;
        $user->forceFill(['view_quota_used' => 0])->save();
        AdminLog::record('reset_quota', $user, ['nama' => $user->full_name, 'terpakai sebelumnya' => $before]);

        return back()->with('status', 'Kuota lihat produk direset.');
    }

    public function anonymize(User $user, AnonymizeCustomer $anonymizeCustomer): RedirectResponse
    {
        abort_unless($user->role === UserRole::Customer && ! $user->anonymized_at, 404);

        $name = $user->full_name;
        $anonymizeCustomer->handle($user);
        AdminLog::record('anonymize_customer', $user, ['nama' => $name]);

        return redirect()->route('admin.users.show', $user)->with('status', 'Data pribadi customer dihapus. Riwayat order tetap tersimpan.');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function withTracking(Builder $query): Builder
    {
        return $query
            ->select('users.*')
            ->withCount('activityLogs')
            ->withMax(['activityLogs as last_login_at' => fn ($logs) => $logs->where('action', 'login')], 'created_at')
            ->addSelect([
                'orders_count' => DB::table('orders')->selectRaw('count(*)')->whereColumn('orders.user_id', 'users.id'),
                'email_deletions' => IdentityRecord::query()->select('deletion_count')
                    ->where('kind', IdentityRecord::KIND_EMAIL)->whereColumn('value', 'users.email')->limit(1),
            ]);
    }
}