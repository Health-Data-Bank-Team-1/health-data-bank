<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportModerationController extends Controller
{
    public function index()
    {
        $reports = FormSubmission::with(['account', 'formTemplate'])
            ->where('status', 'FLAGGED')
            ->latest('flagged_at')
            ->paginate(10);

        return view('livewire.admin.flagged', compact('reports'));
    }

    public function show(FormSubmission $report)
    {
        $report->load([
            'account',
            'formTemplate.fields',
            'healthEntries',
        ]);

        return view('livewire.admin.report-review', compact('report'));
    }

    public function delete(Request $request, FormSubmission $report)
    {
        $validated = $request->validate([
            'deletion_reason' => ['required', 'string', 'min:10'],
            'confirm_delete' => ['required', 'accepted'],
        ]);

        if ($report->trashed()) {
            return redirect()
                ->route('admin.reports.flagged')
                ->with('error', 'Submission is already deleted.');
        }

        DB::transaction(function () use ($report, $validated) {
            $report->update([
                'status' => 'DELETED',
                'deleted_by' => auth()->id(),
                'deletion_reason' => $validated['deletion_reason'],
            ]);

            $report->delete();

            AuditLogger::log(
                'delete_problematic_report',
                ['forms', 'admin', 'moderation', 'outcome:success'],
                $report,
                [],
                [
                    'submission_id' => (string) $report->id,
                    'reason' => $validated['deletion_reason'],
                ]
            );
        });

        return redirect()
            ->route('admin.reports.flagged')
            ->with('success', 'Submission deleted successfully.');
    }
}
