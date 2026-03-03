<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Models\Audit;

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
     * @param  Model|null   $auditable A model instance being acted on (optional)
     * @param  array        $oldValues Safe "before" values (IDs only)
     * @param  array        $newValues Safe "after" values (IDs only)
     */
    public static function log(
        string $event,
        array $tags = [],
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        //don't let obviously sensitive keys through.
        self::guardAgainstSensitiveData($oldValues);
        self::guardAgainstSensitiveData($newValues);

        $user = Auth::user();

        $payload = [
            'user_type'  => $user ? get_class($user) : null,
            'user_id'    => $user ? self::actorIdentifier($user) : null,
            'event'      => $event,
            'old_values' => empty($oldValues) ? [] : $oldValues,
            'new_values' => empty($newValues) ? [] : $newValues,
            'url'        => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 1023),
            'tags'       => empty($tags) ? null : implode(',', $tags),
        ];

        //If an auditable model is provided AND supports manual audit creation, use it.
        if ($auditable && method_exists($auditable, 'audits')) {
            $auditable->audits()->create([
                'user_type'  => $payload['user_type'],
                'user_id'    => $payload['user_id'],
                'event'      => $payload['event'],
                'old_values' => $payload['old_values'],
                'new_values' => $payload['new_values'],
                'url'        => $payload['url'],
                'ip_address' => $payload['ip_address'],
                'user_agent' => $payload['user_agent'],
                'tags'       => $payload['tags'],
            ]);
            return;
        }
        /**
         * Fallback. Write a "system" audit row to the audits table,
         * even when there's no specific auditable model.
         */

        Audit::create([
            'user_type'      => $payload['user_type'],
            'user_id'        => $payload['user_id'],
            'event'          => $payload['event'],
            'auditable_type' => null,
            'auditable_id'   => null,
            'old_values'     => $payload['old_values'],
            'new_values'     => $payload['new_values'],
            'url'            => $payload['url'],
            'ip_address'     => $payload['ip_address'],
            'user_agent'     => $payload['user_agent'],
            'tags'           => $payload['tags'],
        ]);

    }

    /**
     * Prefer auditing by Account id when available, otherwise fall back to auth identifier.
     */
    private static function actorIdentifier($user): string
    {
        if (!empty($user->account_id)) {
            return (string) $user->account_id;
        }

        return (string) $user->getAuthIdentifier();
    }


    /**
     * Guardrail: Prevent logging sensitive content.
     * If a key looks like it could contain PHI, block it.
     */
    private static function guardAgainstSensitiveData(array $data): void
    {
        $blockedKeys = [
            'password', 'pass', 'pwd', 'token', 'secret',
            'email', 'name', 'address', 'dob', 'date_of_birth',
            'health', 'symptom', 'diagnosis', 'notes', 'form_response', 'encrypted_values', 'responses'
        ];

        foreach ($data as $key => $value) {
            $k = strtolower((string) $key);
            foreach ($blockedKeys as $blocked) {
                if (str_contains($k, $blocked)) {
                    //Fail closed: throw to prevent accidental PHI logging.
                    throw new \InvalidArgumentException("AuditLogger blocked sensitive key: {$key}");
                }
            }
            //Also block large free-text blobs
            if (is_string($value) && strlen($value) > 500) {
                throw new \InvalidArgumentException("AuditLogger blocked large text value for key: {$key}");
            }
        }
    }
}
