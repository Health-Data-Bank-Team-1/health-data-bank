<?php
// app/Notifications/ReminderDueNotification.php

namespace App\Notifications;

use Carbon\Carbon;

class ReminderDueNotification implements AppNotification
{
    public function __construct(
        private string $frequency, // daily|weekly|todo
        private Carbon $now,
    ) {}

    public function type(): string
    {
        return 'reminder';
    }

    public function message(): string
    {
        return match ($this->frequency) {
            'daily' => 'Please complete your required health form or data entry for today.',
            'weekly' => 'Please complete your required health form or data entry for this week.',
            'todo' => 'You have not submitted health data recently. Please complete your required form or data entry.',
            default => 'You have a pending reminder.',
        };
    }

    public function link(): ?string
    {
        return match ($this->frequency) {
            'daily' => '/user/form-select',
            'weekly' => '/user/form-select',
            'todo' => '/user/todo',
            default => '/user/dashboard',
        };
    }

    public function dedupeKey(): ?string
    {
        // Dedupe rules:
        // - daily: one per calendar day
        // - weekly: one per week (bucketed by startOfWeek)
        // - todo: one per day (adjust if you want different behavior)
        return match ($this->frequency) {
            'daily' => 'reminder:daily:'.$this->now->toDateString(),
            'weekly' => 'reminder:weekly:'.$this->now->copy()->startOfWeek()->toDateString(),
            'todo' => 'reminder:todo:'.$this->now->toDateString(),
            default => null,
        };
    }
}