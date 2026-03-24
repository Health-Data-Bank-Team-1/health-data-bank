<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Services\AggregatedMetricsService;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Exceptions\CohortSuppressedException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResearcherReportController extends Controller
{
    public function aggregated(
        Request $request,
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold,
        AggregatedMetricsService $aggregator
    ) {
        $validated = $request->validate([
            'cohort_id' => ['nullable', 'uuid'],

            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],

            // direct demographic filters for ad hoc reporting
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:age_min'],
            'gender' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
            'account_status' => ['nullable', 'in:ACTIVE,DEACTIVATED'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
        ]);

        try {
            $cohortName = null;

            if (!empty($validated['cohort_id'])) {
                $cohort = DB::table('researcher_cohorts')
                    ->where('id', $validated['cohort_id'])
                    ->first();

                if (!$cohort) {
                    return response()->json([
                        'message' => 'Cohort not found.',
                    ], 404);
                }

                $filters = json_decode($cohort->filters_json, true) ?? [];
                $cohortName = $cohort->name;
            } else {
                $filters = [
                    'account_type' => 'User',
                    'account_status' => $validated['account_status'] ?? 'ACTIVE',
                    'age_min' => $validated['age_min'] ?? null,
                    'age_max' => $validated['age_max'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'location' => $validated['location'] ?? null,
                    'created_from' => $validated['created_from'] ?? null,
                    'created_to' => $validated['created_to'] ?? null,
                ];

                $filters = array_filter($filters, fn ($value) => $value !== null);
            }

            $keys = [];
            if (!empty($validated['keys'])) {
                $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
            }

            $cohortQuery = $cohortBuilder->build($filters);
            $accountIds = $cohortQuery->pluck('id')->all();

            $threshold->enforce(count($accountIds), 10);

            $from = CarbonImmutable::parse($validated['from'])->startOfDay();
            $to = CarbonImmutable::parse($validated['to'])->endOfDay();

            $metrics = $aggregator->aggregateForCohort(
                $accountIds,
                $from,
                $to,
                $keys
            );

            AuditLogger::log(
                'researcher_aggregated_report_viewed',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'cohort_id' => $validated['cohort_id'] ?? null,
                    'cohort_size' => count($accountIds),
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'keys_count' => count($keys),
                    'filter_keys' => array_keys($filters),
                ]
            );

            return response()->json([
                'message' => 'Aggregated report generated successfully.',
                'data' => [
                    'cohort_id' => $validated['cohort_id'] ?? null,
                    'cohort_name' => $cohortName,
                    'cohort_size' => count($accountIds),
                    'from' => $from->toIso8601String(),
                    'to' => $to->toIso8601String(),
                    'filters_applied' => $filters,
                    'metrics' => $metrics,
                ],
            ]);
        } catch (CohortSuppressedException $e) {
            return response()->json([
                'message' => 'Cohort suppressed due to minimum size rule.',
                'errors' => [
                    'cohort' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate aggregated report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportAggregatedCsv(
        Request $request,
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold,
        AggregatedMetricsService $aggregator
    ): StreamedResponse|\Illuminate\Http\JsonResponse {
        $validated = $request->validate([
            'cohort_id' => ['nullable', 'uuid'],

            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],

            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:age_min'],
            'gender' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:100'],
            'account_status' => ['nullable', 'in:ACTIVE,DEACTIVATED'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
        ]);

        try {
            if (!empty($validated['cohort_id'])) {
                $cohort = DB::table('researcher_cohorts')
                    ->where('id', $validated['cohort_id'])
                    ->first();

                if (!$cohort) {
                    return response()->json([
                        'message' => 'Cohort not found.',
                    ], 404);
                }

                $filters = json_decode($cohort->filters_json, true) ?? [];
            } else {
                $filters = [
                    'account_type' => 'User',
                    'account_status' => $validated['account_status'] ?? 'ACTIVE',
                    'age_min' => $validated['age_min'] ?? null,
                    'age_max' => $validated['age_max'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'location' => $validated['location'] ?? null,
                    'created_from' => $validated['created_from'] ?? null,
                    'created_to' => $validated['created_to'] ?? null,
                ];

                $filters = array_filter($filters, fn ($value) => $value !== null);
            }

            $keys = [];
            if (!empty($validated['keys'])) {
                $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
            }

            $cohortQuery = $cohortBuilder->build($filters);
            $accountIds = $cohortQuery->pluck('id')->all();

            $threshold->enforce(count($accountIds), 10);

            $from = CarbonImmutable::parse($validated['from'])->startOfDay();
            $to = CarbonImmutable::parse($validated['to'])->endOfDay();

            $metrics = $aggregator->aggregateForCohort(
                $accountIds,
                $from,
                $to,
                $keys
            );

            AuditLogger::log(
                'researcher_aggregated_report_exported',
                ['reporting', 'researcher', 'outcome:success', 'format:csv'],
                null,
                [],
                [
                    'cohort_id' => $validated['cohort_id'] ?? null,
                    'cohort_size' => count($accountIds),
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'keys_count' => count($keys),
                    'filter_keys' => array_keys($filters),
                    'format' => 'csv',
                ]
            );

            return response()->streamDownload(function () use ($metrics) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['metric_key', 'count', 'avg']);

                foreach ($metrics as $metricKey => $values) {
                    fputcsv($handle, [
                        $metricKey,
                        $values['count'] ?? 0,
                        $values['avg'] ?? '',
                    ]);
                }

                fclose($handle);
            }, 'researcher_aggregated_report.csv', [
                'Content-Type' => 'text/csv',
            ]);
        } catch (CohortSuppressedException $e) {
            return response()->json([
                'message' => 'Cohort suppressed due to minimum size rule.',
                'errors' => [
                    'cohort' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to export aggregated report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
