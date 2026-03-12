<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Services\TrendCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\AuditLogger;

class TrendController extends Controller
{
    public function index(Request $request, TrendCalculationService $service)
    {
        $validated = $request->validate([
            'metric' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-\.]+$/'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'bucket' => ['sometimes', Rule::in(['day', 'week', 'month'])],
        ]);

        $user = $request->user();
        if (!$user || !$user->account_id) {
            return response()->json([
                'message' => 'User is not linked to an account.',
            ], 422);
        }

        $metric = $validated['metric'];
        $bucket = $validated['bucket'] ?? 'day';

        $from = CarbonImmutable::parse($validated['from'])->startOfDay();
        $to = CarbonImmutable::parse($validated['to'])->endOfDay();

        $out = $service->trendForAccount(
            $user->account_id,
            $metric,
            $from,
            $to,
            $bucket
        );

        AuditLogger::log(
            'reporting_trends_view',
            ['reporting', 'resource:trends', 'outcome:success'],
            null,
            [],
            [
                'metric' => $metric,
                'bucket' => $bucket,
                'from' => $validated['from'],
                'to' => $validated['to'],
            ]
        );

        return response()->json($out);
    }
}
