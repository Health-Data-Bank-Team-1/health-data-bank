<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SummaryQueryRequest;
use App\Services\AuditLogger;
use App\Services\PersonalSummaryService;
use Carbon\Carbon;

class MeSummaryController extends Controller
{
    public function show(SummaryQueryRequest $request, PersonalSummaryService $svc)
    {
        $validated = $request->validated();

        $user = $request->user();
        abort_unless($user?->account_id, 422, 'User has no account attached.');

        $keys = [];
        if ($request->filled('keys')) {
            $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
        }

        AuditLogger::log(
            'reporting_summary_view',
            ['reporting', 'resource:summary'],
            null,
            [],
            [
                'from' => $validated['from'],
                'to' => $validated['to'],
                'keys_count' => count($keys),
            ]
        );

        $result = $svc->summary(
            $user->account_id,
            Carbon::parse($validated['from']),
            Carbon::parse($validated['to']),
            $keys
        );

        return response()->json($result);
    }
}