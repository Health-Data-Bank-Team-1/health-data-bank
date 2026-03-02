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
    public function exportTrendsCsv(Request $request): StreamedResponse
    {
        // Validate query params
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

        // Default date range: last 90 days
        $dateTo = isset($validated['date_to']) ? $validated['date_to'] : now()->toDateString();
        $dateFrom = isset($validated['date_from']) ? $validated['date_from'] : now()->subDays(90)->toDateString();

        // Resolve accounts.uuid for current user (matches your project pattern)
        $user = $request->user();
        $accountId = null;

        if ($user && !empty($user->email)) {
            $accountId = DB::table('accounts')->where('email', $user->email)->value('id');
        }

        if (!$accountId) {
            AuditLogger::log(
                'report_export_failed',
                'blocked',
                'account_mapping_failed',
                'report',
                'dashboard_trends',
                [],
                null
            );

            abort(403, 'Account mapping failed.');
        }

        // Audit: export requested
        AuditLogger::log(
            'report_export_requested',
            'success',
            null,
            'report',
            'dashboard_trends',
            [
                'metric' => $metric,
                'group_by' => $groupBy,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'form_template_id' => $validated['form_template_id'] ?? null,
                'format' => 'csv',
            ],
            $accountId
        );

        $filename = "dashboard_trends_{$metric}_{$groupBy}_{$dateFrom}_{$dateTo}.csv";

        return response()->streamDownload(function () use ($accountId, $groupBy, $dateFrom, $dateTo, $validated) {
            try {
                $out = fopen('php://output', 'w');

                // CSV header
                fputcsv($out, ['period', 'value']);

                // Build query (submission_count only for now)
                $query = DB::table('form_submissions')
                    ->where('account_id', $accountId)
                    ->whereBetween(DB::raw('DATE(submitted_at)'), [$dateFrom, $dateTo]);

                if (!empty($validated['form_template_id'])) {
                    $query->where('form_template_id', $validated['form_template_id']);
                }

                // Grouping
                if ($groupBy === 'day') {
                    $periodExpr = DB::raw("DATE(submitted_at)");
                    $periodLabel = 'period';
                } elseif ($groupBy === 'month') {
                    $periodExpr = DB::raw("DATE_FORMAT(submitted_at, '%Y-%m')");
                    $periodLabel = 'period';
                } else { // week default
                    // ISO week label like 2026-W07
                    $periodExpr = DB::raw("CONCAT(LEFT(YEARWEEK(submitted_at, 3), 4), '-W', RIGHT(YEARWEEK(submitted_at, 3), 2))");
                    $periodLabel = 'period';
                }

                $rows = $query
                    ->select([
                        DB::raw($periodExpr . " as {$periodLabel}"),
                        DB::raw("COUNT(*) as value"),
                    ])
                    ->groupBy($periodLabel)
                    ->orderBy($periodLabel)
                    ->get();

                foreach ($rows as $r) {
                    fputcsv($out, [$r->period, $r->value]);
                }

                fclose($out);

                AuditLogger::log(
                    'report_export_completed',
                    'success',
                    null,
                    'report',
                    'dashboard_trends',
                    ['row_count' => $rows->count()],
                    $accountId
                );
            } catch (Throwable $e) {
                AuditLogger::log(
                    'report_export_failed',
                    'failure',
                    'server_error',
                    'report',
                    'dashboard_trends',
                    [],
                    $accountId
                );

            }
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
