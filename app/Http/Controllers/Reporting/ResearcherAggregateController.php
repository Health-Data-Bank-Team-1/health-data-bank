<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Services\AggregatedMetricsService;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ResearcherAggregateController extends Controller
{
    public function index(
        Request $request,
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold,
        AggregatedMetricsService $aggregator
    ) {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],
            'account_type' => ['sometimes', 'in:User,Researcher,HealthcareProvider,Admin'],
            'account_status' => ['sometimes', 'in:ACTIVE,DEACTIVATED'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
        ]);

        $keys = [];
        if (!empty($validated['keys'])) {
            $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
        }

        $cohortQuery = $cohortBuilder->build($validated);
        $accountIds = $cohortQuery->pluck('id')->unique()->values()->all();

        $threshold->enforce(count($accountIds), 10);

        $from = CarbonImmutable::parse($validated['from'])->startOfDay();
        $to = CarbonImmutable::parse($validated['to'])->endOfDay();

        $metrics = $aggregator->aggregateForCohort(
            $accountIds,
            $from,
            $to,
            $keys
        );

        return response()->json([
            'cohort_size' => count($accountIds),
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'metrics' => $metrics,
        ]);
    }
}
