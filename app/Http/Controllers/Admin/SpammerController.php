<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SpammerReason;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Spammer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SpammerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['blocked', 'watchlist'])],
        ]);

        $spammers = Spammer::query()
            ->with(['ips' => fn ($query) => $query->orderByDesc('last_attempt_at')])
            ->when($filters['q'] ?? null, fn ($query, string $search) => $query->where(
                fn ($query) => $query->where('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%"),
            ))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.spammers.index', compact('spammers', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'no_hp' => $this->normalizePhone((string) $request->input('no_hp')),
            'ip_addresses' => preg_split('/\R/', trim((string) $request->input('ip_addresses')), -1, PREG_SPLIT_NO_EMPTY),
        ]);

        $validated = $request->validate([
            'email' => ['nullable', 'required_without:no_hp', 'email', 'max:255'],
            'no_hp' => ['nullable', 'required_without:email', 'regex:/^62[0-9]{8,13}$/'],
            'reason' => ['required', Rule::in(array_column(SpammerReason::cases(), 'value'))],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'ip_addresses' => ['nullable', 'array', 'max:10'],
            'ip_addresses.*' => ['nullable', 'ip', Rule::notIn(array_filter([$this->requestIp()]))],
            'attempt_count' => ['nullable', 'integer', 'between:1,3'],
        ]);

        $duplicate = Spammer::query()
            ->where(function ($query) use ($validated): void {
                if (! empty($validated['email'])) {
                    $query->where('email', $validated['email']);
                }
                if (! empty($validated['no_hp'])) {
                    $method = empty($validated['email']) ? 'where' : 'orWhere';
                    $query->{$method}('no_hp', $validated['no_hp']);
                }
            })
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'email' => 'Email atau nomor HP ini sudah ada di daftar spammer.',
            ]);
        }

        $spammer = DB::transaction(function () use ($validated): Spammer {
            $spammer = Spammer::create([
                'email' => $validated['email'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'reason' => $validated['reason'],
                'status' => 'blocked',
                'admin_note' => $validated['admin_note'] ?? null,
            ]);

            foreach (array_unique(array_filter($validated['ip_addresses'] ?? [])) as $ipAddress) {
                $spammer->ips()->create([
                    'ip_address' => $ipAddress,
                    'attempt_count' => $validated['attempt_count'] ?? 1,
                    'last_attempt_at' => now(),
                ]);
            }

            $spammer->logs()->create([
                'action' => 'manually_blocked',
                'ip_address' => $this->requestIp(),
                'note' => $validated['admin_note'] ?? null,
            ]);

            AdminLog::record(
                'spammer_created',
                Spammer::class,
                $spammer->id,
                new: ['email' => $spammer->email, 'no_hp' => $spammer->no_hp],
                description: 'Admin menambahkan identitas ke daftar spammer.',
            );

            return $spammer;
        });

        return redirect()->route('office.spammers.index')
            ->with('success', 'Identitas '.($spammer->email ?: $spammer->no_hp).' berhasil diblokir.');
    }

    public function unblock(Spammer $spammer): RedirectResponse
    {
        DB::transaction(function () use ($spammer): void {
            $spammer = Spammer::query()->lockForUpdate()->findOrFail($spammer->id);

            if (! $spammer->isBlocked() || $spammer->unblock_count >= 2) {
                throw ValidationException::withMessages([
                    'spammer' => 'Spammer ini tidak dapat dibuka blokirnya lagi.',
                ]);
            }

            $spammer->ips()->delete();
            $spammer->update([
                'status' => 'watchlist',
                'unblock_count' => $spammer->unblock_count + 1,
                'unblocked_until' => now()->addDays(7),
            ]);
            $spammer->logs()->create([
                'action' => 'unblocked',
                'ip_address' => $this->requestIp(),
                'note' => 'Masa grace period 7 hari dimulai.',
            ]);

            AdminLog::record(
                'spammer_unblocked',
                Spammer::class,
                $spammer->id,
                old: ['status' => 'blocked', 'unblock_count' => $spammer->unblock_count - 1],
                new: ['status' => 'watchlist', 'unblock_count' => $spammer->unblock_count, 'unblocked_until' => $spammer->unblocked_until?->toISOString()],
                description: 'Admin membuka blokir selama 7 hari; riwayat IP dihapus.',
            );
        });

        return back()->with('success', 'Blokir dibuka selama 7 hari dan riwayat IP dihapus.');
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return str_starts_with($digits, '8') ? '62'.$digits : $digits;
    }

    private function requestIp(): ?string
    {
        $ip = function_exists('get_real_ip') ? get_real_ip() : request()->ip();

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
