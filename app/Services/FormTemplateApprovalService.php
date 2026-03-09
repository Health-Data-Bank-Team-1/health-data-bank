<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Models\User;
use App\Services\AuditLogger;
use App\Exceptions\WorkflowException;

class FormTemplateApprovalService
{
    /**
     * @throws WorkflowException
     */
    public function submitForApproval(FormTemplate $template): void
    {
        if ($template->approval_status !== 'draft') {
            throw new WorkflowException(
                'Only draft templates can be submitted for approval.'
            );
        }

        $old = ['approval_status' => $template->approval_status];

        $template->update([
            'approval_status' => 'pending'
        ]);

        AuditLogger::log(
            'form_template_submitted',
            ['form', 'workflow'],
            $template,
            $old,
            ['approval_status' => 'pending']
        );
    }

    /**
     * @throws WorkflowException
     */
    public function approve(FormTemplate $template, User $admin): void
    {
        if ($template->approval_status !== 'pending') {
            throw new WorkflowException(
                'Only pending templates can be approved.'
            );
        }

        $old = ['approval_status' => $template->approval_status];

        $template->update([
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => null
        ]);

        AuditLogger::log(
            'form_template_approved',
            ['form', 'workflow', 'outcome:approved'],
            $template,
            $old,
            ['approval_status' => 'approved']
        );

        // Create immutable version snapshot
        $this->createVersionSnapshot($template, $admin->id);
    }

    /**
     * @throws WorkflowException
     */
    public function reject(FormTemplate $template, User $admin, string $reason): void
    {
        if ($template->approval_status !== 'pending') {
            throw new WorkflowException(
                'Only pending templates can be rejected.'
            );
        }

        $old = ['approval_status' => $template->approval_status];

        $template->update([
            'approval_status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => $reason
        ]);

        AuditLogger::log(
            'form_template_rejected',
            ['form', 'workflow', 'outcome:rejected'],
            $template,
            $old,
            ['approval_status' => 'rejected']
        );
    }

    /**
     * Create immutable version snapshot
     */
    private function createVersionSnapshot(FormTemplate $template, string $adminId): void
    {
        $nextVersion = FormTemplateVersion::where('form_template_id', $template->id)->max('version') ?? 0;

        FormTemplateVersion::create([
            'form_template_id' => $template->id,
            'version' => $nextVersion + 1,

            'title' => $template->title,
            'schema' => $template->schema,

            'created_by' => $adminId,
        ]);
    }
}
