<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DashboardReportController extends Controller
{
    public function trends(Request $request)
    {
        $validated = $request->validate([
            'metric' => 'nullable|string|in:submission_count',
            'group_by' => 'nullable|string|in:day,week,month',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'form_template_id' => 'nullable',
        ]);

        $metric = $validated['metric'] ?? 'submission_count';
        $groupBy = $validated['group_by'] ?? 'week';
        $dateTo = $validated['date_to'] ?? now()->toDateString();
        $dateFrom = $validated['date_from'] ?? now()->subDays(90)->toDateString();

        $user = $request->user();
        $accountId = $user?->account_id;

        if (!$accountId) {
            AuditLogger::log(
                'dashboard_trends_view_blocked',
                ['reporting', 'resource:dashboard_trends', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        try {
            $query = DB::table('form_submissions')
                ->where('account_id', $accountId)
                ->whereBetween(DB::raw('DATE(submitted_at)'), [$dateFrom, $dateTo]);

            if (!empty($validated['form_template_id'])) {
                $query->where('form_template_id', $validated['form_template_id']);
            }

            if ($groupBy === 'day') {
                $periodExpr = "DATE(submitted_at)";
                $rows = $query
                    ->select([
                        DB::raw("$periodExpr as period"),
                        DB::raw("COUNT(*) as value"),
                    ])
                    ->groupBy(DB::raw($periodExpr))
                    ->orderBy(DB::raw($periodExpr))
                    ->get();
            } elseif ($groupBy === 'month') {
                $periodExpr = "DATE_FORMAT(submitted_at, '%Y-%m')";
                $rows = $query
                    ->select([
                        DB::raw("$periodExpr as period"),
                        DB::raw("COUNT(*) as value"),
                    ])
                    ->groupBy(DB::raw($periodExpr))
                    ->orderBy(DB::raw($periodExpr))
                    ->get();
            } else {
                $sortExpr = "YEARWEEK(submitted_at, 3)";
                $labelExpr = "CONCAT(LEFT(YEARWEEK(submitted_at, 3), 4), '-W', RIGHT(YEARWEEK(submitted_at, 3), 2))";

                $rows = $query
                    ->select([
                        DB::raw("$labelExpr as period"),
                        DB::raw("COUNT(*) as value"),
                        DB::raw("$sortExpr as sort_key"),
                    ])
                    ->groupBy('period', 'sort_key')
                    ->orderBy('sort_key')
                    ->get();
            }

            AuditLogger::log(
                'dashboard_trends_view_requested',
                ['reporting', 'resource:dashboard_trends', 'outcome:success'],
                null,
                [],
                [
                    'metric' => $metric,
                    'group_by' => $groupBy,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'form_template_id' => $validated['form_template_id'] ?? null,
                    'format' => 'json',
                ]
            );

            return response()->json([
                'metric' => $metric,
                'group_by' => $groupBy,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'labels' => $rows->pluck('period')->values(),
                'values' => $rows->pluck('value')->values(),
            ]);
        } catch (Throwable $e) {
            AuditLogger::log(
                'dashboard_trends_view_failed',
                ['reporting', 'resource:dashboard_trends', 'outcome:failure'],
                null,
                [],
                ['reason_code' => 'server_error']
            );

            throw $e;
        }
    }

    public function exportTrendsCsv(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'metric' => 'nullable|string|in:submission_count',
            'group_by' => 'nullable|string|in:day,week,month',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'form_template_id' => 'nullable',
        ]);

        $metric = $validated['metric'] ?? 'submission_count';
        if ($metric !== 'submission_count') {
            abort(422, 'Unsupported metric.');
        }

        $groupBy = $validated['group_by'] ?? 'week';
        $dateTo = $validated['date_to'] ?? now()->toDateString();
        $dateFrom = $validated['date_from'] ?? now()->subDays(90)->toDateString();

        $user = $request->user();
        $accountId = $user?->account_id;

        if (!$accountId) {
            AuditLogger::log(
                'dashboard_trends_export_blocked',
                ['reporting', 'resource:dashboard_trends', 'outcome:blocked'],
                null,
                [],
                ['reason_code' => 'account_mapping_failed']
            );

            abort(403, 'Account mapping failed.');
        }

        AuditLogger::log(
            'dashboard_trends_export_requested',
            ['reporting', 'resource:dashboard_trends', 'outcome:success'],
            null,
            [],
            [
                'metric' => $metric,
                'group_by' => $groupBy,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'form_template_id' => $validated['form_template_id'] ?? null,
                'format' => 'csv',
            ]
        );

        $filename = "dashboard_trends_{$metric}_{$groupBy}_{$dateFrom}_{$dateTo}.csv";

        return response()->streamDownload(function () use ($accountId, $groupBy, $dateFrom, $dateTo, $validated, $metric) {
            try {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['period', 'value']);

                $query = DB::table('form_submissions')
                    ->where('account_id', $accountId)
                    ->whereBetween(DB::raw('DATE(submitted_at)'), [$dateFrom, $dateTo]);

                if (!empty($validated['form_template_id'])) {
                    $query->where('form_template_id', $validated['form_template_id']);
                }

                if ($groupBy === 'day') {
                    $periodExpr = "DATE(submitted_at)";
                    $rows = $query
                        ->select([
                            DB::raw("$periodExpr as period"),
                            DB::raw("COUNT(*) as value"),
                        ])
                        ->groupBy(DB::raw($periodExpr))
                        ->orderBy(DB::raw($periodExpr))
                        ->get();
                } elseif ($groupBy === 'month') {
                    $periodExpr = "DATE_FORMAT(submitted_at, '%Y-%m')";
                    $rows = $query
                        ->select([
                            DB::raw("$periodExpr as period"),
                            DB::raw("COUNT(*) as value"),
                        ])
                        ->groupBy(DB::raw($periodExpr))
                        ->orderBy(DB::raw($periodExpr))
                        ->get();
                } else {
                    $sortExpr = "YEARWEEK(submitted_at, 3)";
                    $labelExpr = "CONCAT(LEFT(YEARWEEK(submitted_at, 3), 4), '-W', RIGHT(YEARWEEK(submitted_at, 3), 2))";

                    $rows = $query
                        ->select([
                            DB::raw("$labelExpr as period"),
                            DB::raw("COUNT(*) as value"),
                            DB::raw("$sortExpr as sort_key"),
                        ])
                        ->groupBy('period', 'sort_key')
                        ->orderBy('sort_key')
                        ->get();
                }

                foreach ($rows as $r) {
                    fputcsv($out, [$r->period, $r->value]);
                }

                fclose($out);

                AuditLogger::log(
                    'dashboard_trends_export_completed',
                    ['reporting', 'resource:dashboard_trends', 'outcome:success'],
                    null,
                    [],
                    [
                        'metric' => $metric,
                        'group_by' => $groupBy,
                        'format' => 'csv',
                        'row_count' => $rows->count(),
                    ]
                );
            } catch (Throwable $e) {
                AuditLogger::log(
                    'dashboard_trends_export_failed',
                    ['reporting', 'resource:dashboard_trends', 'outcome:failure'],
                    null,
                    [],
                    ['reason_code' => 'server_error']
                );

                throw $e;
            }
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
