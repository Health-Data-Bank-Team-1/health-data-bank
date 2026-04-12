<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrendQueryRequest;
use App\Services\TrendCalculationService;
use App\Services\AuditLogger;
use Carbon\CarbonImmutable;

class TrendController extends Controller
{
    public function index(TrendQueryRequest $request, TrendCalculationService $service)
    {
        $validated = $request->validated();

        $user = $request->user();
        if (!$user || !$user->account_id) {
            return response()->json([
                'message' => 'User is not linked to an account.',
            ], 422);
        }

        $metric = $validated['metric'];
        $from = CarbonImmutable::parse($validated['from']);
        $to = CarbonImmutable::parse($validated['to']);
        $bucket = $validated['bucket'] ?? 'day';

        AuditLogger::log(
            'reporting_trends_view',
            ['reporting', 'resource:trends', 'outcome:success'],
            null,
            [],
            [
                'metric' => $metric,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'bucket' => $bucket,
            ]
        );

        $result = $service->calculate(
            $user->account_id,
            $metric,
            $from,
            $to,
            $bucket
        );

        return response()->json($result);
    }
}