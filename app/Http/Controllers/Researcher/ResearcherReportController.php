<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\AuditLogger;
use App\Models\Report;
use App\Models\AggregatedData;

class ResearcherReportController extends Controller
{
    public function aggregated(Request $request)
    {
        $filters = $request->all();

        try {
            $baseQuery = DB::table('health_goals')
                ->join('accounts', 'health_goals.account_id', '=', 'accounts.id');

            if (!empty($filters['metric_key'])) {
                $baseQuery->where('health_goals.metric_key', $filters['metric_key']);
            }

            if (!empty($filters['status'])) {
                $baseQuery->where('health_goals.status', $filters['status']);
            }

            if (!empty($filters['timeframe'])) {
                $baseQuery->where('health_goals.timeframe', $filters['timeframe']);
            }

            if (!empty($filters['start_date'])) {
                $baseQuery->whereDate('health_goals.start_date', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $baseQuery->whereDate('health_goals.end_date', '<=', $filters['end_date']);
            }

            $rows = (clone $baseQuery)->select(
                'health_goals.metric_key',
                'health_goals.status',
                'health_goals.target_value'
            )->get();

            $report = [
                'cohort_size' => $rows->count(),
                'active_goals' => $rows->where('status', 'ACTIVE')->count(),
                'expired_goals' => $rows->where('status', 'EXPIRED')->count(),
                'average_target_value' => round($rows->avg('target_value') ?? 0, 2),
                'metric_breakdown' => $rows->groupBy('metric_key')->map(function ($group) {
                    return $group->count();
                })->toArray(),
            ];

            AuditLogger::log(
                'researcher_aggregated_report_viewed',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'filters' => $filters,
                    'cohort_size' => $report['cohort_size'],
                ]
            );

            return response()->json([
                'message' => 'Aggregated report generated successfully',
                'filters_applied' => $filters,
                'report' => $report,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate aggregated report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportAggregatedCsv(Request $request): StreamedResponse
    {
        $filters = $request->all();

        $baseQuery = DB::table('health_goals')
            ->join('accounts', 'health_goals.account_id', '=', 'accounts.id');

        if (!empty($filters['metric_key'])) {
            $baseQuery->where('health_goals.metric_key', $filters['metric_key']);
        }

        if (!empty($filters['status'])) {
            $baseQuery->where('health_goals.status', $filters['status']);
        }

        if (!empty($filters['timeframe'])) {
            $baseQuery->where('health_goals.timeframe', $filters['timeframe']);
        }

        if (!empty($filters['start_date'])) {
            $baseQuery->whereDate('health_goals.start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $baseQuery->whereDate('health_goals.end_date', '<=', $filters['end_date']);
        }

        $rows = (clone $baseQuery)->select(
            'health_goals.metric_key',
            'health_goals.status',
            'health_goals.target_value'
        )->get();

        $report = [
            'cohort_size' => $rows->count(),
            'active_goals' => $rows->where('status', 'ACTIVE')->count(),
            'expired_goals' => $rows->where('status', 'EXPIRED')->count(),
            'average_target_value' => round($rows->avg('target_value') ?? 0, 2),
            'metric_breakdown' => $rows->groupBy('metric_key')->map(function ($group) {
                return $group->count();
            })->toArray(),
        ];

        AuditLogger::log(
            'researcher_aggregated_report_exported',
            ['reporting', 'researcher', 'outcome:success', 'format:csv'],
            null,
            [],
            [
                'filters' => $filters,
                'cohort_size' => $report['cohort_size'],
                'format' => 'csv',
            ]
        );

        $filename = 'aggregated_report.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['metric', 'value']);
            fputcsv($handle, ['cohort_size', $report['cohort_size']]);
            fputcsv($handle, ['active_goals', $report['active_goals']]);
            fputcsv($handle, ['expired_goals', $report['expired_goals']]);
            fputcsv($handle, ['average_target_value', $report['average_target_value']]);

            foreach ($report['metric_breakdown'] as $metric => $count) {
                fputcsv($handle, ["metric_breakdown_{$metric}", $count]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
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

            if (!$report) {
                return response()->json([
                    'message' => 'Report not found or not owned by researcher'
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
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to append report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}