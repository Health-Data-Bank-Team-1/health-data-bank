<?php

namespace App\Jobs;

use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\ReminderSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledReminders implements ShouldQueue
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

            if (! $isComplete && ! $this->duplicateReminderExists($accountId, $frequency, $now)) {
                Notification::create([
                    'account_id' => $accountId,
                    'type' => 'reminder',
                    'message' => $this->buildMessage($frequency),
                    'status' => 'unread',
                ]);

                $reminder->last_sent_at = $now;
            }

            $reminder->next_run_at = $this->calculateNextRunAt($frequency, $now);
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

    protected function hasSubmittedThisWeek(string $accountId): bool
    {
        return FormSubmission::query()
            ->where('account_id', $accountId)
            ->whereBetween('submitted_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->exists();
    }

    protected function hasRecentSubmission(string $accountId): bool
    {
        $threeDaysAgo = now()->subDays(3);

        return FormSubmission::query()
            ->where('account_id', $accountId)
            ->where('submitted_at', '>=', $threeDaysAgo)
            ->exists();
    }

    protected function duplicateReminderExists(string $accountId, string $frequency, Carbon $now): bool
    {
        return match ($frequency) {
            'daily' => Notification::query()
                ->where('account_id', $accountId)
                ->where('type', 'reminder')
                ->whereDate('created_at', $now->toDateString())
                ->exists(),

            'weekly' => Notification::query()
                ->where('account_id', $accountId)
                ->where('type', 'reminder')
                ->whereBetween('created_at', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek(),
                ])
                ->exists(),

            'todo' => Notification::query()
                ->where('account_id', $accountId)
                ->where('type', 'reminder')
                ->whereDate('created_at', $now->toDateString())
                ->exists(),

            default => false,
        };
    }

    protected function calculateNextRunAt(string $frequency, Carbon $now): Carbon
    {
        return match ($frequency) {
            'daily' => $now->copy()->addDay(),
            'weekly' => $now->copy()->addWeek(),
            'todo' => $now->copy()->addDay(),
            default => $now->copy()->addDay(),
        };
    }
    protected function buildMessage(string $frequency): string
    {
        return match ($frequency) {
            'daily' => 'Please complete your required health form or data entry for today.',
            'weekly' => 'Please complete your required health form or data entry for this week.',
            'todo' => 'You have not submitted health data recently. Please complete your required form or data entry.',
            default => 'You have a pending reminder.',
        };
    }
}
