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
                'weekly' => $this->hasSubmittedThisWeek($accountId),
                'todo' => $this->hasRecentSubmission($accountId),
                default => true,
            };

            if (! $isComplete) {
                $delivery = app(NotificationDeliveryService::class);

                $delivery->deliverToAccount(
                    $accountId,
                    new ReminderDueNotification($frequency, Carbon::parse($now))
                );

                $reminder->last_sent_at = $now;
            }

            $reminder->next_run_at = $this->calculateNextRunAt($frequency, $now);
            $reminder->save();
        }
    }

    // ... keep the rest of your existing helper methods (hasSubmittedToday, etc.)
}