<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        abort_if(Gate::denies('admin-access'), 403);

        $auditTable = config('audit.drivers.database.table', 'audits');

        $totals = [
            'audit_entries' => DB::table($auditTable)->count(),

            'auth_events' => DB::table($auditTable)
                ->whereIn('event', [
                    'login_success',
                    'login_failure',
                    'register_success',
                    'logout',
                ])
                ->count(),

            'access_denied_events' => DB::table($auditTable)
                ->where('event', 'access_denied')
                ->count(),

            'report_views' => DB::table($auditTable)
                ->whereIn('event', [
                    'reporting_trends_view',
                    'reporting_summary_view',
                    'researcher_aggregated_report_viewed',
                ])
                ->count(),

            'report_exports' => DB::table($auditTable)
                ->whereIn('event', [
                    'researcher_aggregated_report_exported',
                ])
                ->count(),

            'cohort_generation_events' => DB::table($auditTable)
                ->where('event', 'researcher_cohort_generated')
                ->count(),

            'form_workflow_events' => DB::table($auditTable)
                ->whereIn('event', [
                    'form_submission_success',
                    'form_template_submitted',
                    'form_template_approved',
                    'form_template_rejected',
                ])
                ->count(),
        ];

        $recentActivity = DB::table($auditTable)
            ->select([
                'id',
                'event',
                'user_type',
                'user_id',
                'auditable_type',
                'auditable_id',
                'url',
                'ip_address',
                'tags',
                'created_at',
            ])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'totals' => $totals,
            'recent_activity' => $recentActivity,
        ]);
    }
}
