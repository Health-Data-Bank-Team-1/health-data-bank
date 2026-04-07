<?php

namespace App\Notifications;

interface AppNotification
{
    /** Machine-readable type (e.g., reminder, alert, report_update, etc.) */
    public function type(): string;

    /** Human message shown in the in-app notifications list */
    public function message(): string;

    /** Optional link to redirect when notification is opened */
    public function link(): ?string;

    /**
     * Stable dedupe key. If non-null, duplicates should not be stored for the same account.
     * Example: "reminder:daily:2026-04-07"
     */
    public function dedupeKey(): ?string;
}