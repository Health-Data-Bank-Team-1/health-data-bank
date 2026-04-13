<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportModerationController extends Controller
{
    /**
     * Archive a report
     */
    public function archive(Request $request, Report $report)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        // Check if already archived
        if ($report->is_archived) {
            return response()->json([
                'success' => false,
                'message' => 'Report is already archived',
            ], 400);
        }

        $report->update([
            'is_archived' => true,
            'archive_reason' => $validated['reason'],
            'archived_by' => auth()->user()->id,
            'archived_at' => now(),
            'moderation_status' => 'archived',
            'moderation_reason' => $validated['reason'],
            'moderated_by' => auth()->user()->id,
            'moderated_at' => now(),
        ]);

        AuditLogger::log(
            'report_archived',
            ['reporting', 'admin', 'moderation', 'outcome:success'],
            $report,
            [],
            ['reason' => $validated['reason']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Report archived successfully',
            'data' => $report->fresh(),
        ], 200);
    }

    /**
     * Show all flagged reports
     */
    public function index()
    {
        $reports = Report::where('moderation_status', 'FLAGGED')
            ->latest('moderated_at')
            ->paginate(10);

        return view('livewire.admin.flagged', compact('reports'));
    }

    /**
     * Show a single report for review
     */
    public function show(Report $report)
    {
        $report->load(['researcher', 'updates']);

        return view('livewire.admin.report-review', compact('report'));
    }

    /**
     * Delete a report (soft delete)
     */
    public function delete(Request $request, string $report)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        $reportModel = Report::withTrashed()->find($report);

        if (!$reportModel) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found',
            ], 404);
        }

        if ($reportModel->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Report is already deleted',
            ], 400);
        }

        $reportModel->update([
            'deletion_reason' => $validated['reason'],
            'deleted_by' => auth()->user()->id,
            'moderation_status' => 'deleted',
            'moderation_reason' => $validated['reason'],
            'moderated_by' => auth()->user()->id,
            'moderated_at' => now(),
        ]);

        $reportModel->delete();

        AuditLogger::log(
            'report_deleted',
            ['reporting', 'admin', 'moderation', 'outcome:success'],
            $reportModel,
            [],
            ['reason' => $validated['reason']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully',
            'data' => $reportModel->fresh(),
        ], 200);
    }

    /**
     * Restore a soft-deleted report
     */
    public function restore(Request $request, string $report)
    {
        $validated = $request->validate([
            'reason' => ['sometimes', 'string', 'max:500'],
        ]);

        $reportModel = Report::withTrashed()->find($report);

        if (!$reportModel) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found',
            ], 404);
        }

        if (!$reportModel->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Report is not deleted',
            ], 400);
        }

        $reportModel->restore();

        $reportModel->update([
            'restoration_reason' => $validated['reason'] ?? null,
            'restored_by' => auth()->user()->id,
            'restored_at' => now(),
            'moderation_status' => 'active',
            'moderated_by' => auth()->user()->id,
            'moderated_at' => $reportModel->moderated_at
                ? $reportModel->moderated_at->copy()->addSecond()
                : now(),
        ]);

        AuditLogger::log(
            'report_restored',
            ['reporting', 'admin', 'moderation', 'outcome:success'],
            $reportModel,
            [],
            ['reason' => $validated['reason'] ?? null]
        );

        return response()->json([
            'success' => true,
            'message' => 'Report restored successfully',
            'data' => $reportModel->fresh(),
        ], 200);
    }

    /**
     * Get moderation status for a report
     */
    public function status(string $report)
    {
        $reportData = Report::withTrashed()->find($report);

        if (!$reportData) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found',
            ], 404);
        }

        $status = 'active';
        if ($reportData->is_archived) {
            $status = 'archived';
        } elseif ($reportData->trashed()) {
            $status = 'deleted';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reportData->id,
                'status' => $status,
                'is_archived' => (bool) $reportData->is_archived,
                'is_deleted' => (bool) $reportData->deleted_at,
                'is_approved' => (bool) $reportData->is_approved,
                'archived_at' => $reportData->archived_at,
                'deleted_at' => $reportData->deleted_at,
                'archived_by' => $reportData->archived_by,
                'deleted_by' => $reportData->deleted_by,
                'moderated_by' => $reportData->moderated_by,
                'moderated_at' => $reportData->moderated_at,
            ],
        ], 200);
    }

    /**
     * Permanently delete a report (hard delete)
     */
    public function permanentDelete(Request $request, Report $report)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:20'],
            'confirmed' => ['required', 'boolean', 'accepted'],
        ]);

        $reportId = $report->id;

        // Delete related aggregated data
        DB::table('aggregated_data')->where('report_id', $reportId)->delete();

        // Hard delete the report
        $report->forceDelete();

        AuditLogger::log(
            'report_permanently_deleted',
            ['reporting', 'admin', 'moderation', 'outcome:success'],
            $report,
            [],
            ['reason' => $validated['reason']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Report permanently deleted',
        ], 200);
    }
}
