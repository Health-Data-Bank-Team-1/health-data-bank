<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ReportModerationService;
use Illuminate\Http\Request;

class ReportModerationController extends Controller
{
    protected $moderationService;

    public function __construct(ReportModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Archive a report
     */
    public function archive(Request $request, Report $report)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $result = $this->moderationService->archiveReport(
            $report,
            $request->user(),
            $validated['reason']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Delete (soft delete) a report
     */
    public function delete(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $result = $this->moderationService->deleteReport(
            $report,
            $request->user(),
            $validated['reason']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Restore a deleted report
     */
    public function restore(Request $request, $reportId)
    {
        $report = Report::withTrashed()->findOrFail($reportId);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->moderationService->restoreReport(
            $report,
            $request->user(),
            $validated['reason'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get moderation status
     */
    public function status(Request $request, $reportId)
    {
        $report = Report::withTrashed()->findOrFail($reportId);

        $status = $this->moderationService->getModerationStatus($report);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Permanently delete a report (hard delete)
     */
    public function permanentDelete(Request $request, $reportId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:20|max:500',
            'confirmed' => 'required|boolean|in:1,true',
        ]);

        $report = Report::withTrashed()->findOrFail($reportId);

        $result = $this->moderationService->permanentlyDeleteReport(
            $report,
            $request->user(),
            $validated['reason']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}