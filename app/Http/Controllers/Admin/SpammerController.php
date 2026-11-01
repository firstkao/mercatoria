<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Customer;
use App\Models\ProductView;
use App\Models\Spammer;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpammerController extends Controller
{
    public function index(): View
    {
        return view('admin.spammers.index', [
            'spammers' => Spammer::orderByDesc('id')->paginate(30),
        ]);
    }

    /**
     * PRD 10.7: inject spammer manual via /office/spammers.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'no_hp' => ['required', 'regex:/^(?:\+?62|0)8[1-9][0-9]{6,11}$/'],
        ]);

        $data['no_hp'] = normalize_phone($data['no_hp']);
        $data['reason'] = Spammer::REASON_MANUAL;
        $data['status'] = Spammer::STATUS_BLOCKED;
        $data['banned_at'] = now();

        $spammer = Spammer::create($data);
        AdminLog::record($request->user(), 'create', 'Spammer', $spammer->id, null, collect($data)->except('password')->all(), 'Inject spammer manual');

        // PRD 41.1: bell admin — spammer baru.
        app(AdminNotificationService::class)->notify(
            'spammer_baru',
            'Spammer baru: '.$spammer->email,
            '/office/spammers'
        );

        return redirect()->route('office.spammers.index')->with('status', 'Spammer ditambahkan.');
    }

    /**
     * PRD 10: unblock manual — status ke watchlist, unblock_count bertambah.
     */
    public function unblock(Request $request, Spammer $spammer): RedirectResponse
    {
        [$old, $new] = AdminLog::diff($spammer, ['status', 'unblock_count'], [
            'status' => Spammer::STATUS_WATCHLIST,
            'unblock_count' => $spammer->unblock_count + 1,
        ]);

        $spammer->update([
            'status' => Spammer::STATUS_WATCHLIST,
            'unblock_count' => $spammer->unblock_count + 1,
        ]);

        AdminLog::record($request->user(), 'update', 'Spammer', $spammer->id, $old, $new, 'Unblock spammer manual');

        $this->startGrace($spammer);

        return redirect()->route('office.spammers.index')->with('status', 'Spammer di-unblock.');
    }

    /**
     * PRD 7.8: setelah unblock, Customer Blocked masuk state Grace 7 hari (view di-reset 10/10).
     * Setelah 2x gagal (unblock_count >= 2) → Blocked Permanen, tidak ada Grace lagi.
     */
    private function startGrace(Spammer $spammer): void
    {
        $customer = Customer::query()
            ->where('current_state', 'Blocked')
            ->where('unblock_count', '<', 2)
            ->where(fn ($q) => $q->where('email', $spammer->email)->orWhere('no_hp', $spammer->no_hp))
            ->first();

        if (! $customer) {
            return;
        }

        $customer->update([
            'current_state' => 'Grace',
            'unblock_grace_until' => now()->addDays(7),
            'state_updated_at' => now(),
        ]);

        ProductView::where('user_id', $customer->user_id)->delete();
    }
}
