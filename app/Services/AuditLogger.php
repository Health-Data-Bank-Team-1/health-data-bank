<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditLogger
 *
 * Central wrapper for consistent audit logging across the system.
 * Uses owen-it/laravel-auditing (audits table).
 *
 * Important: Never log raw health values, form responses, or direct identifiers.
 * Only log IDs, event codes, and minimal safe metadata.
 */
class AuditLogger
{
    /**
     * Log an auditable event.
     *
     * @param  string       $event    Event name in snake_case (e.g., login_success)
     * @param  array        $tags     Tags for category/outcome (e.g., ['auth','outcome:success'])
     * @param  mixed|null   $auditable A model instance being acted on (optional)
     * @param  array        $oldValues Safe "before" values (IDs only)
     * @param  array        $newValues Safe "after" values (IDs only)
     */
    public static function log(
        string $event,
        array $tags = [],
        $auditable = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        // Basic safety: don't let obviously sensitive keys through.
        self::guardAgainstSensitiveData($oldValues);
        self::guardAgainstSensitiveData($newValues);

        $user = Auth::user();


        if ($auditable && method_exists($auditable, 'audits')) {
            // Create an audit record via relationship so auditable_type/auditable_id are set correctly.
            $auditable->audits()->create([
                'user_type'  => $user ? get_class($user) : null,
                'user_id'    => $user ? $user->getAuthIdentifier() : null,
                'event'      => $event,
                'old_values' => empty($oldValues) ? null : json_encode($oldValues),
                'new_values' => empty($newValues) ? null : json_encode($newValues),
                'url'        => Request::fullUrl(),
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 1023),
                'tags'       => empty($tags) ? null : implode(',', $tags),
            ]);
            return;
        }

    }

    /**
     * Guardrail: Prevent logging sensitive content.
     * This is conservative: if a key looks like it could contain PHI, block it.
     */
    private static function guardAgainstSensitiveData(array $data): void
    {
        $blockedKeys = [
            'password', 'pass', 'pwd', 'token', 'secret',
            'email', 'name', 'address', 'dob', 'date_of_birth',
            'health', 'symptom', 'diagnosis', 'notes', 'form_response', 'responses'
        ];

        foreach ($data as $key => $value) {
            $k = strtolower((string) $key);
            foreach ($blockedKeys as $blocked) {
                if (str_contains($k, $blocked)) {
                    // Fail closed: throw to prevent accidental PHI logging.
                    throw new \InvalidArgumentException("AuditLogger blocked sensitive key: {$key}");
                }
            }
            // Also block large free-text blobs
            if (is_string($value) && strlen($value) > 500) {
                throw new \InvalidArgumentException("AuditLogger blocked large text value for key: {$key}");
            }
        }
    }
}
