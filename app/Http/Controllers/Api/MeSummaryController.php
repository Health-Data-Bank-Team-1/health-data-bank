<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\HealthMetricRegistry;
use App\Services\PersonalSummaryService;
use App\Services\SuggestionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MeSummaryController extends Controller
{
    public function show(
        Request $request,
        PersonalSummaryService $svc,
        SuggestionService $suggestions,
        HealthMetricRegistry $metrics
    ) {
        $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'keys' => ['sometimes', 'string'],
        ]);

        $user = $request->user();
        abort_unless($user?->account_id, 422, 'User has no account attached.');

        $keys = [];
        if ($request->filled('keys')) {
            $requestedKeys = array_values(array_filter(array_map('trim', explode(',', $request->keys))));

            $keys = array_values(array_filter(
                $requestedKeys,
                fn (string $key) => $metrics->hasMetric($key)
            ));
        }

        $from = Carbon::parse($request->from);
        $to = Carbon::parse($request->to);

        $summary = $svc->summaryForAccount(
            $user->account_id,
            $from,
            $to,
            $keys
        );

        $suggestionPayload = $suggestions->generateForAccount(
            $user->account_id,
            $from,
            $to,
            $keys
        );

        AuditLogger::log(
            'reporting_summary_view',
            ['reporting', 'resource:summary', 'outcome:success'],
            null,
            [],
            [
                'from' => $request->from,
                'to' => $request->to,
                'keys' => $keys,
                'suggestion_count' => count($suggestionPayload['suggestions'] ?? []),
            ]
        );

        return response()->json([
            ...$summary,
            'suggestions' => $suggestionPayload['suggestions'] ?? [],
        ]);
    }
}
