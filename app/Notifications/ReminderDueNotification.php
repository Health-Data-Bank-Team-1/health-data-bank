<?php

namespace App\Notifications;

use Carbon\CarbonInterface;

class ReminderDueNotification implements AppNotification
{
    public function __construct(
        private string $frequency, // daily|weekly|todo
        private CarbonInterface $now,
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
            'daily', 'weekly' => '/user/form-select',
            'todo' => '/user/todo',
            default => '/user/dashboard',
        };
    }

    public function dedupeKey(): ?string
    {
        return match ($this->frequency) {
            'daily' => 'reminder:daily:'.$this->now->toDateString(),
            'weekly' => 'reminder:weekly:'.$this->now->copy()->startOfWeek()->toDateString(),
            'todo' => 'reminder:todo:'.$this->now->toDateString(),
            default => null,
        };
    }
}