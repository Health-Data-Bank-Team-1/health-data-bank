<?php

namespace App\Http\Controllers\Researcher;

use App\Exceptions\CohortSuppressedException;
use App\Http\Controllers\Controller;
use App\Models\AggregatedData;
use App\Models\Report;
use App\Services\AggregatedMetricsService;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
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
            'cohort_id' => ['required', 'uuid'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],
        ]);

        try {
            $cohort = DB::table('researcher_cohorts')
                ->where('id', $validated['cohort_id'])
                ->first();

            if (! $cohort) {
                return response()->json([
                    'message' => 'Cohort not found.',
                ], 404);
            }

            $filters = json_decode($cohort->filters_json, true) ?? [];

            $keys = [];
            if (! empty($validated['keys'])) {
                $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
            }

            $cohortQuery = $cohortBuilder->build($filters);
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

            AuditLogger::log(
                'researcher_aggregated_report_viewed',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'cohort_id' => $validated['cohort_id'],
                    'cohort_size' => count($accountIds),
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'keys_count' => count($keys),
                ]
            );

            return response()->json([
                'message' => 'Aggregated report generated successfully.',
                'data' => [
                    'cohort_id' => $validated['cohort_id'],
                    'cohort_name' => $cohort->name,
                    'cohort_size' => count($accountIds),
                    'from' => $from->toIso8601String(),
                    'to' => $to->toIso8601String(),
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
            AuditLogger::log(
                'researcher_aggregated_report_failed',
                ['reporting', 'researcher', 'outcome:failure'],
                null,
                [],
                ['reason_code' => 'server_error']
            );

            return response()->json([
                'message' => 'Failed to generate aggregated report.',
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
            'cohort_id' => ['required', 'uuid'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],
        ]);

        try {
            $cohort = DB::table('researcher_cohorts')
                ->where('id', $validated['cohort_id'])
                ->first();

            if (! $cohort) {
                return response()->json([
                    'message' => 'Cohort not found.',
                ], 404);
            }

            $filters = json_decode($cohort->filters_json, true) ?? [];

            $keys = [];
            if (! empty($validated['keys'])) {
                $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
            }

            $cohortQuery = $cohortBuilder->build($filters);
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

            AuditLogger::log(
                'researcher_aggregated_report_exported',
                ['reporting', 'researcher', 'outcome:success', 'format:csv'],
                null,
                [],
                [
                    'cohort_id' => $validated['cohort_id'],
                    'cohort_size' => count($accountIds),
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'keys_count' => count($keys),
                    'format' => 'csv',
                ]
            );

            $filename = 'researcher_aggregated_report.csv';

            return response()->streamDownload(function () use ($metrics) {
                $handle = fopen('php://output', 'w');
                // Add UTF-8 BOM for spreadsheet compatibility.
                fwrite($handle, "\xEF\xBB\xBF");

                fputcsv($handle, ['metric_key', 'count', 'avg']);

                foreach ($metrics as $metricKey => $values) {
                    fputcsv($handle, [
                        $metricKey,
                        $values['count'] ?? 0,
                        $values['avg'] ?? '',
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache',
            ]);
        } catch (CohortSuppressedException $e) {
            return response()->json([
                'message' => 'Cohort suppressed due to minimum size rule.',
                'errors' => [
                    'cohort' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $e) {
            AuditLogger::log(
                'researcher_aggregated_report_export_failed',
                ['reporting', 'researcher', 'outcome:failure', 'format:csv'],
                null,
                [],
                ['reason_code' => 'server_error']
            );

            return response()->json([
                'message' => 'Failed to export aggregated report.',
            ], 500);
        }
    }

    public function exportReportCsv(Request $request, string $reportId): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $report = Report::find($reportId);

            if (! $report) {
                return response()->json([
                    'message' => 'Report not found.',
                ], 404);
            }

            $metrics = [];
            $data = $report->aggregatedData;
            if ($data instanceof \Illuminate\Support\Collection) {
                $data = $data->toArray();
            }

            foreach (is_array($data) ? $data : [] as $row) {
                if (is_array($row) && isset($row['metrics']) && is_array($row['metrics'])) {
                    $metrics = $row['metrics'];
                    break;
                }
                if (is_object($row) && isset($row->metrics) && is_array($row->metrics)) {
                    $metrics = $row->metrics;
                    break;
                }
            }

            AuditLogger::log(
                'researcher_report_exported',
                ['reporting', 'researcher', 'outcome:success', 'format:csv'],
                null,
                [],
                [
                    'report_id' => $report->id,
                    'format' => 'csv',
                ]
            );

            $filename = "report_{$report->id}.csv";

            return response()->streamDownload(function () use ($metrics) {
                $handle = fopen('php://output', 'w');

                fputcsv($handle, ['Metric', 'Value']);

                foreach ($metrics as $key => $value) {
                    fputcsv($handle, [
                        $key,
                        is_array($value) ? json_encode($value) : $value,
                    ]);
                }

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to export report.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function append(Request $request, string $reportId)
    {
        $validated = $request->validate([
            'metrics' => ['required', 'array'],
            'anonymization_level' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $user = $request->user();

            $report = Report::where('id', $reportId)
                ->where('researcher_id', $user->account_id)
                ->first();

            if (! $report) {
                return response()->json([
                    'message' => 'Report not found or not owned by researcher',
                ], 404);
            }

            $data = AggregatedData::create([
                'report_id' => $report->id,
                'metrics' => $validated['metrics'],
                'anonymization_level' => $validated['anonymization_level'] ?? 1,
            ]);

            AuditLogger::log(
                'researcher_report_appended',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'report_id' => $report->id,
                    'aggregated_data_id' => $data->id,
                ]
            );

            return response()->json([
                'message' => 'Report appended successfully',
                'report_id' => $report->id,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to append report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
