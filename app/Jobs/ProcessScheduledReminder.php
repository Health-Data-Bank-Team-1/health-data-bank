<?php

namespace App\Jobs;

use App\Models\FormSubmission;
use App\Models\ReminderSetting;
use App\Notifications\ReminderDueNotification;
use App\Services\Notifications\NotificationDeliveryService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledReminders
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        $dueReminders = ReminderSetting::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->get();

        foreach ($dueReminders as $reminder) {
            $accountId = $reminder->account_id;
            $frequency = $reminder->frequency;

            $isComplete = match ($frequency) {
                'daily' => $this->hasSubmittedToday($accountId),
                'weekly' => $this->hasSubmittedThisWeek($accountId, $now),
                'todo' => $this->hasRecentSubmission($accountId, $now),
                default => true,
            };

            if (! $isComplete) {
                $delivery = app(NotificationDeliveryService::class);

                // Deliver in-app notification (DB-backed) using centralized logic
                $delivery->deliverToAccount(
                    $accountId,
                    new ReminderDueNotification($frequency, Carbon::parse($now))
                );

                $reminder->last_sent_at = $now;
            }

            $reminder->next_run_at = $this->calculateNextRunAt($frequency, Carbon::parse($now));
            $reminder->save();
        }
    }

    protected function hasSubmittedToday(string $accountId): bool
    {
        return FormSubmission::query()
            ->where('account_id', $accountId)
            ->whereDate('submitted_at', today())
            ->exists();
    }

    protected function hasSubmittedThisWeek(string $accountId, Carbon $now): bool
    {
        return FormSubmission::query()
            ->where('account_id', $accountId)
            ->whereBetween('submitted_at', [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ])
            ->exists();
    }

    protected function hasRecentSubmission(string $accountId, Carbon $now): bool
    {
        $threeDaysAgo = $now->copy()->subDays(3);

        return FormSubmission::query()
            ->where('account_id', $accountId)
            ->where('submitted_at', '>=', $threeDaysAgo)
            ->exists();
    }

    protected function calculateNextRunAt(string $frequency, Carbon $now): Carbon
    {
        return match ($frequency) {
            'daily' => $now->copy()->addDay(),
            'weekly' => $now->copy()->addWeek(),
            // "todo" reminders: keep checking daily (adjust if your product wants different cadence)
            'todo' => $now->copy()->addDay(),
            default => $now->copy()->addDay(),
        };
    }
}