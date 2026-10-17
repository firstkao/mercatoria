<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\UserGift;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Services\Notification\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class CustomerGiftController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'customer' && (! $user->isCrush() || $user->customer()->exists()), 404);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['coin', 'voucher', 'gift_box'])],
            'coin_amount' => ['required_if:type,coin', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'voucher_id' => ['required_if:type,voucher', 'nullable', 'integer', 'exists:vouchers,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $voucher = isset($validated['voucher_id']) ? Voucher::findOrFail($validated['voucher_id']) : null;
        if ($voucher && ! $voucher->isValid()) {
            return back()->withErrors(['voucher_id' => 'Voucher tidak aktif atau belum/tidak berlaku saat ini.']);
        }

        $gift = DB::transaction(function () use ($user, $validated, $voucher): UserGift {
            if ($validated['type'] === 'coin') {
                $coin = Coin::query()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
                $coin->increment('balance', (int) $validated['coin_amount']);
                $coin->refresh();
                CoinTransaction::create([
                    'user_id' => $user->id,
                    'amount' => (int) $validated['coin_amount'],
                    'source' => CoinSource::ADMIN_GIFT->value,
                    'description' => $validated['note'] ?? 'Hadiah koin dari admin.',
                    'balance_after' => $coin->balance,
                    'status' => 'active',
                ]);
            }

            if ($voucher) {
                UserVoucher::create([
                    'user_id' => $user->id,
                    'voucher_id' => $voucher->id,
                    'status' => 'active',
                ]);
            }

            $gift = UserGift::create([
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'type' => $validated['type'],
                'coin_amount' => $validated['coin_amount'] ?? null,
                'voucher_id' => $voucher?->id,
                'note' => $validated['note'] ?? null,
            ]);

            UserNotificationService::push(
                $user->id,
                NotificationType::GIFT,
                'Hadiah dari Mercatoria',
                match ($validated['type']) {
                    'coin' => number_format((int) $validated['coin_amount']).' koin telah ditambahkan ke akunmu.',
                    'voucher' => 'Voucher '.$voucher->code.' telah ditambahkan ke akunmu.',
                    default => 'Admin mengirimkan hadiah khusus untukmu.',
                },
                route('dashboard'),
                subtype: 'admin_gift',
                meta: ['gift_id' => $gift->id, 'type' => $validated['type']],
            );

            AdminLog::record(
                'admin_send_gift',
                UserGift::class,
                $gift->id,
                new: ['user_id' => $user->id, 'type' => $gift->type, 'coin_amount' => $gift->coin_amount, 'voucher_id' => $gift->voucher_id],
                description: 'Admin memberikan hadiah langsung kepada customer.',
            );

            return $gift;
        });

        $emailError = $this->sendGiftEmail($user, $gift);
        $response = back()->with('success', 'Hadiah berhasil diberikan dan notifikasi bell sudah dikirim.');

        return $emailError
            ? $response->with('warning', 'Email notifikasi gagal dikirim; kegagalan telah dicatat pada log email.')
            : $response;
    }

    private function sendGiftEmail(User $user, UserGift $gift): bool
    {
        $template = EmailTemplate::getByCode('user_gift_notification');
        if (! $template) {
            EmailLog::create([
                'template_id' => null,
                'recipient_email' => $user->email,
                'subject' => 'Hadiah dari Mercatoria',
                'status' => 'failed',
                'error_message' => 'Active user_gift_notification template is missing.',
            ]);

            return true;
        }

        $subject = str_replace('{{nama_user}}', $user->name, $template->subject);
        $body = str_replace(
            ['{{nama_user}}', '{{email}}', '{{total}}', '{{status}}', '{{link}}', '{{tanggal}}', '{{nama_toko}}'],
            [e($user->name), e($user->email), e((string) ($gift->coin_amount ?? $gift->voucher?->code ?? 'Gift khusus')), 'Hadiah diterima', e(route('dashboard')), now()->format('d-m-Y'), 'Mercatoria'],
            $template->body,
        );

        try {
            Mail::html($body, function ($message) use ($template, $user, $subject): void {
                $message->from($template->from_email, 'Mercatoria')
                    ->to($user->email)
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            EmailLog::create([
                'template_id' => $template->id,
                'recipient_email' => $user->email,
                'subject' => $subject,
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
            report($exception);

            return true;
        }

        EmailLog::create([
            'template_id' => $template->id,
            'recipient_email' => $user->email,
            'subject' => $subject,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return false;
    }
}
