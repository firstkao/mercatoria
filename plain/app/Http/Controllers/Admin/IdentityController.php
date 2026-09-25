<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\AdminLog;
use App\Models\IdentityRecord;
use App\Models\NameBlacklistEntry;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IdentityController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['diblokir', 'diawasi', 'semua'], true) ? $request->query('status') : null;
        $search = trim((string) $request->query('q'));
        $limit = Setting::integer('registration_limit', 3);

        $records = IdentityRecord::query()
            ->select('identity_records.*')
            ->withCount(['registrationEvents as registered_count' => fn ($events) => $events->where('event', 'registered')])
            ->withMin('registrationEvents as first_registered_at', 'created_at')
            ->withMax('registrationEvents as last_event_at', 'created_at')
            ->addSelect(['account_id' => User::query()->select('users.id')
                ->where(fn ($query) => $query
                    ->where(fn ($q) => $q->whereColumn('users.email', 'identity_records.value')->where('identity_records.kind', IdentityRecord::KIND_EMAIL))
                    ->orWhere(fn ($q) => $q->whereColumn('users.whatsapp', 'identity_records.value')->where('identity_records.kind', IdentityRecord::KIND_WHATSAPP)))
                ->limit(1)])
            ->when($status === 'diblokir', fn ($query) => $query->where(fn ($q) => $q->whereNotNull('blocked_at')->orWhere('deletion_count', '>=', $limit)))
            ->when($status === 'diawasi', fn ($query) => $query->whereNull('blocked_at')->whereBetween('deletion_count', [1, max(1, $limit - 1)]))
            ->when($status === null && $search === '', fn ($query) => $query->where(fn ($q) => $q->whereNotNull('blocked_at')->orWhere('deletion_count', '>', 0)))
            ->when($search !== '', fn ($query) => $query->where('value', 'like', '%'.ltrim($search, '+0').'%'))
            ->orderByRaw('case when blocked_at is null then 1 else 0 end')
            ->orderByDesc('deletion_count')
            ->latest('updated_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.identities.index', [
            'records' => $records,
            'limit' => $limit,
            'filters' => ['status' => $status, 'q' => $search],
            ...$this->tabCounts($limit),
        ]);
    }

    public function reset(IdentityRecord $identity): RedirectResponse
    {
        $before = $identity->deletion_count;

        DB::transaction(function () use ($identity): void {
            $identity->forceFill(['deletion_count' => 0, 'blocked_at' => null])->save();
            $identity->registrationEvents()->create(['event' => 'reset']);
        });

        AdminLog::record('reset_identity', $identity, ['identitas' => $identity->value, 'terhapus sebelumnya' => $before]);

        return back()->with('status', "{$identity->value} direset dan bisa mendaftar lagi dengan jatah penuh.");
    }

    public function block(IdentityRecord $identity): RedirectResponse
    {
        DB::transaction(function () use ($identity): void {
            $identity->forceFill(['blocked_at' => now()])->save();
            $identity->registrationEvents()->create(['event' => 'blocked']);
        });

        AdminLog::record('block_identity', $identity, ['identitas' => $identity->value]);

        return back()->with('status', "{$identity->value} diblokir.");
    }

    /**
     * Block an email or number that has no record yet.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', 'in:email,whatsapp'],
            'value' => ['required', 'string', 'max:255'],
        ], [], ['value' => 'email atau nomor']);

        $value = $data['kind'] === IdentityRecord::KIND_WHATSAPP
            ? RegisterRequest::normalizeWhatsapp($data['value'])
            : Str::lower(trim($data['value']));

        return $this->block(IdentityRecord::firstOrCreate(['kind' => $data['kind'], 'value' => $value]));
    }

    /**
     * @return array{blockedCount: int, nameCount: int}
     */
    public static function tabCounts(int $limit): array
    {
        return [
            'blockedCount' => IdentityRecord::query()->where(fn ($q) => $q->whereNotNull('blocked_at')->orWhere('deletion_count', '>=', $limit))->count(),
            'nameCount' => NameBlacklistEntry::count(),
        ];
    }
}