<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;

class ReportModerationService
{
    /**
     * Archive a report
     */
    public function archiveReport(Report $report, User $user, string $reason): array
    {
        if ($report->moderation_status === 'archived') {
            return [
                'success' => false,
                'message' => 'Report is already archived',
            ];
        }

        $report->moderation_status = 'archived';
        $report->moderation_reason = $reason;
        $report->moderated_by = $user->id;
        $report->moderated_at = now();
        $report->save();

        return [
            'success' => true,
            'message' => 'Report archived successfully',
            'data' => $report,
        ];
    }

    /**
     * Delete (soft delete) a report
     */
    public function deleteReport(Report $report, User $user, string $reason): array
    {
        if ($report->deleted_at !== null) {
            return [
                'success' => false,
                'message' => 'Report is already deleted',
            ];
        }

        $report->moderation_status = 'deleted';
        $report->moderation_reason = $reason;
        $report->moderated_by = $user->id;
        $report->moderated_at = now();
        $report->save();

        $report->delete();

        return [
            'success' => true,
            'message' => 'Report deleted successfully',
            'data' => $report,
        ];
    }

    /**
     * Restore a deleted report
     */
    public function restoreReport(Report $report, User $user, ?string $reason = null): array
    {
        if ($report->deleted_at === null) {
            return [
                'success' => false,
                'message' => 'Report is not deleted',
            ];
        }

        $report->restore();

        $report->moderation_status = 'approved';
        $report->moderation_reason = $reason;
        $report->moderated_by = $user->id;
        $report->moderated_at = now();
        $report->save();

        return [
            'success' => true,
            'message' => 'Report restored successfully',
            'data' => $report,
        ];
    }

    /**
     * Permanently delete a report (hard delete)
     */
    public function permanentlyDeleteReport(Report $report, User $user, string $reason): array
    {
        // Delete related aggregated data
        $report->aggregatedData()->delete();

        // Hard delete the report
        $report->forceDelete();

        return [
            'success' => true,
            'message' => 'Report permanently deleted',
        ];
    }

    /**
     * Get moderation status
     */
    public function getModerationStatus(Report $report): array
    {
        return [
            'id' => $report->id,
            'status' => $report->moderation_status,
            'is_archived' => $report->moderation_status === 'archived' || $report->deleted_at !== null,
            'is_approved' => $report->moderation_status === 'approved' && $report->deleted_at === null,
            'reason' => $report->moderation_reason,
            'moderated_by' => $report->moderated_by,
            'moderated_at' => $report->moderated_at,
            'deleted_at' => $report->deleted_at,
        ];
    }
}