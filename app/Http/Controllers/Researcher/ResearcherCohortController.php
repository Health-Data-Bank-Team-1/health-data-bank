<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class ResearcherCohortController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->all();

        try {
            $query = DB::table('health_goals')
                ->join('accounts', 'health_goals.account_id', '=', 'accounts.id');

            if (!empty($filters['metric_key'])) {
                $query->where('health_goals.metric_key', $filters['metric_key']);
            }

            if (!empty($filters['status'])) {
                $query->where('health_goals.status', $filters['status']);
            }

            if (!empty($filters['timeframe'])) {
                $query->where('health_goals.timeframe', $filters['timeframe']);
            }

            if (!empty($filters['start_date'])) {
                $query->whereDate('health_goals.start_date', '>=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $query->whereDate('health_goals.end_date', '<=', $filters['end_date']);
            }

            $results = $query->select(
                'health_goals.id',
                'health_goals.account_id',
                'health_goals.metric_key',
                'health_goals.comparison_operator',
                'health_goals.target_value',
                'health_goals.timeframe',
                'health_goals.start_date',
                'health_goals.end_date',
                'health_goals.status'
            )->get();

            AuditLogger::log(
                'researcher_cohort_generated',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'filters' => $filters,
                    'cohort_size' => $results->count(),
                ]
            );

            return response()->json([
                'message' => 'Cohort generated successfully',
                'cohort_size' => $results->count(),
                'filters_applied' => $filters,
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate cohort',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
