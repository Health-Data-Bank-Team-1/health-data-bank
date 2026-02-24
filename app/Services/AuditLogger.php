<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(
        string $actionType,
        ?string $outcome = null,
        ?string $reasonCode = null,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = [],
        ?string $actorIdOverride = null
    ): void {

        try {
            $metadata = self::sanitizeMetadata($metadata);

            [$resolvedActorId, $actorRole] = self::resolveActor();
            $actorId = $actorIdOverride ?? $resolvedActorId;

            if ($actorRole) {
                $metadata['actor_role'] = $actorRole;
            }

            $row = [
                'actor_id'    => $actorId,
                'action_type' => $actionType,
                'outcome'     => $outcome,
                'reason_code' => $reasonCode,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip_address'  => Request::ip(),
                'user_agent'  => substr((string) Request::userAgent(), 0, 1023),
                'metadata'    => empty($metadata) ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
                'timestamp'   => now(), // or use created_at if you standardize on timestamps()
            ];

            DB::table('audit_logs')->insert($row);
        } catch (\Throwable $e) {
            //  record internal error
            Log::warning('AuditLogger failed to write audit log', [
                'action_type' => $actionType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private static function resolveActor(): array
    {
        $user = Auth::user();
        if (!$user || empty($user->email)) {
            return [null, null];
        }

        $account = DB::table('accounts')
            ->select('id', 'role') // adjust column name if needed
            ->where('email', $user->email)
            ->first();

        return [$account?->id, $account?->role];
    }

    private static function sanitizeMetadata(array $data): array
    {
        // Hard block keys that are *very likely* PII/PHI or secrets
        $blockedKeys = [
            'password', 'pass', 'pwd', 'token', 'secret',
            'email', 'name', 'address', 'phone', 'dob', 'date_of_birth',
            'answers', 'answer', 'response', 'responses', 'payload', 'body', 'content',
            'notes', 'note', 'comment', 'message',
        ];

        $out = [];
        $sanitized = false;

        foreach ($data as $key => $value) {
            $k = strtolower((string) $key);

            foreach ($blockedKeys as $blocked) {
                if (str_contains($k, $blocked)) {
                    $sanitized = true;
                    continue 2; // skip this key
                }
            }

            // Block long strings (likely free text)
            if (is_string($value) && strlen($value) > 300) {
                $sanitized = true;
                continue;
            }

            // Block large arrays (often full request bodies)
            if (is_array($value) && count($value) > 50) {
                $sanitized = true;
                continue;
            }

            $out[$key] = $value;
        }

        if ($sanitized) {
            $out['metadata_sanitized'] = true;
        }

        return $out;
    }
}
