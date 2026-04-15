<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
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
            $cacheKey = implode(':', [
                'dashboard_trends',
                $accountId,
                $metric,
                $groupBy,
                $dateFrom,
                $dateTo,
                $validated['form_template_id'] ?? 'all',
            ]);

            $payload = Cache::remember($cacheKey, now()->addMinutes(10), function () use (
                $accountId,
                $metric,
                $groupBy,
                $dateFrom,
                $dateTo,
                $validated
            ) {
                $rows = $this->buildTrendRows(
                    $accountId,
                    $dateFrom,
                    $dateTo,
                    $groupBy,
                    $validated['form_template_id'] ?? null
                );

                return [
                    'metric' => $metric,
                    'group_by' => $groupBy,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'labels' => $rows->pluck('period')->values(),
                    'values' => $rows->pluck('value')->values(),
                ];
            });

            AuditLogger::log(
                'dashboard_trends_viewed',
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

            return response()->json($payload);
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

        return response()->streamDownload(function () use (
            $accountId,
            $groupBy,
            $dateFrom,
            $dateTo,
            $validated,
            $metric
        ) {
            try {
                $out = fopen('php://output', 'w');
                // Add UTF-8 BOM for spreadsheet compatibility.
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['period', 'value']);

                $rows = $this->buildTrendRows(
                    $accountId,
                    $dateFrom,
                    $dateTo,
                    $groupBy,
                    $validated['form_template_id'] ?? null
                );

                foreach ($rows as $row) {
                    fputcsv($out, [$row->period, $row->value]);
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
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    private function buildTrendRows(
        string $accountId,
        string $dateFrom,
        string $dateTo,
        string $groupBy,
        mixed $formTemplateId = null
    ): Collection {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $query = DB::table('form_submissions')
            ->where('account_id', $accountId)
            ->whereBetween('submitted_at', [$from, $to]);

        if (!empty($formTemplateId)) {
            $query->where('form_template_id', $formTemplateId);
        }

        if ($groupBy === 'day') {
            $periodExpr = "DATE(submitted_at)";

            return $query
                ->select([
                    DB::raw("$periodExpr as period"),
                    DB::raw("COUNT(*) as value"),
                ])
                ->groupBy(DB::raw($periodExpr))
                ->orderBy(DB::raw($periodExpr))
                ->get();
        }

        if ($groupBy === 'month') {
            $periodExpr = "DATE_FORMAT(submitted_at, '%Y-%m')";

            return $query
                ->select([
                    DB::raw("$periodExpr as period"),
                    DB::raw("COUNT(*) as value"),
                ])
                ->groupBy(DB::raw($periodExpr))
                ->orderBy(DB::raw($periodExpr))
                ->get();
        }

        $sortExpr = "YEARWEEK(submitted_at, 3)";
        $labelExpr = "CONCAT(LEFT(YEARWEEK(submitted_at, 3), 4), '-W', RIGHT(YEARWEEK(submitted_at, 3), 2))";

        return $query
            ->select([
                DB::raw("$labelExpr as period"),
                DB::raw("COUNT(*) as value"),
                DB::raw("$sortExpr as sort_key"),
            ])
            ->groupBy('period', 'sort_key')
            ->orderBy('sort_key')
            ->get();
    }
}
