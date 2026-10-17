<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\UserState;
use App\Models\Customer;
use App\Models\OrderStatusLog;
use App\Services\Notification\UserNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class UpdateCustomerStates extends Command
{
    protected $signature = 'user:update-state';

    protected $description = 'Update customer states based on active orders and the last ended order';

    public function handle(): int
    {
        $activeStatuses = array_map(
            fn (OrderStatus $status): string => $status->value,
            OrderStatus::activeStatuses(),
        );
        $endedStatuses = array_map(
            fn (OrderStatus $status): string => $status->value,
            OrderStatus::endedStatuses(),
        );
        $counts = ['checked' => 0, 'changed' => 0, 'warnings' => 0];

        Customer::query()->with('user')->chunkById(100, function ($customers) use (
            $activeStatuses,
            $endedStatuses,
            &$counts,
        ): void {
            foreach ($customers as $customer) {
                $counts['checked']++;
                $user = $customer->user;
                $activeOrder = $user->orders()
                    ->whereIn('status', $activeStatuses)
                    ->latest('created_at')
                    ->first();

                if ($customer->unblocked_until?->isFuture()) {
                    $targetState = UserState::MVP;
                    $lastEndedAt = null;
                } elseif ($activeOrder) {
                    $targetState = UserState::MVP;
                    $lastEndedAt = null;
                    $customer->last_order_at = $activeOrder->created_at;
                } else {
                    $lastEndedAt = OrderStatusLog::query()
                        ->whereIn('status', $endedStatuses)
                        ->whereHas('order', fn ($query) => $query->where('user_id', $user->id))
                        ->latest('created_at')
                        ->value('created_at');

                    if ($lastEndedAt) {
                        $lastEndedAt = Carbon::parse($lastEndedAt);
                    } else {
                        $lastEndedOrder = $user->orders()
                            ->whereIn('status', $endedStatuses)
                            ->latest('updated_at')
                            ->first();
                        if (! $lastEndedOrder) {
                            continue;
                        }
                        $lastEndedAt = $lastEndedOrder->hangus_at ?? $lastEndedOrder->updated_at;
                    }

                    if (! $lastEndedAt) {
                        continue;
                    }
                    $elapsedDays = (int) $lastEndedAt->diffInDays(now());
                    $targetState = match (true) {
                        $elapsedDays < 30 => UserState::EX,
                        $elapsedDays <= 60 => UserState::ONS,
                        default => UserState::BLOCKED,
                    };
                }

                $oldState = UserState::from($customer->current_state);
                if ($oldState !== $targetState) {
                    $customer->current_state = $targetState->value;
                    $customer->state_updated_at = now();
                    $customer->save();
                    UserNotificationService::push(
                        $user->id,
                        NotificationType::STATE,
                        'Status akun diperbarui',
                        'Status akunmu sekarang '.$targetState->label().'.',
                        route('dashboard'),
                        subtype: 'user_state_changed',
                        meta: ['old_state' => $oldState->value, 'new_state' => $targetState->value],
                    );
                    $counts['changed']++;
                } elseif ($customer->isDirty('last_order_at')) {
                    $customer->save();
                }

                if (! $lastEndedAt) {
                    continue;
                }

                $warningTarget = match ($targetState) {
                    UserState::EX => [UserState::ONS, 23],
                    UserState::ONS => [UserState::BLOCKED, 53],
                    default => null,
                };
                if (! $warningTarget || $lastEndedAt->copy()->addDays($warningTarget[1])->isFuture()) {
                    continue;
                }

                $subtype = 'state_warning_'.$warningTarget[0]->value;
                $warningExists = $user->notifications()
                    ->where('subtype', $subtype)
                    ->where('created_at', '>=', $lastEndedAt)
                    ->exists();
                if ($warningExists) {
                    continue;
                }

                UserNotificationService::push(
                    $user->id,
                    NotificationType::STATE,
                    'Perubahan status akun dalam 7 hari',
                    'Status akunmu akan berubah menjadi '.$warningTarget[0]->label().' dalam 7 hari jika belum ada pesanan aktif.',
                    route('dashboard'),
                    subtype: $subtype,
                    meta: ['next_state' => $warningTarget[0]->value],
                );
                $counts['warnings']++;
            }
        });

        $this->info(sprintf(
            'Customer states checked=%d, changed=%d, warnings=%d.',
            $counts['checked'],
            $counts['changed'],
            $counts['warnings'],
        ));

        return self::SUCCESS;
    }
}
