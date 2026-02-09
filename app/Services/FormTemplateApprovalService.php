<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Validation\ValidationException;

class FormTemplateApprovalService
{
    public function submitForApproval(FormTemplate $template): void
    {
        if ($template->approval_status !== 'draft') {
            throw ValidationException::withMessages([
                'approval_status' => 'Only draft templates can be submitted for approval.'
            ]);
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

    public function approve(FormTemplate $template, User $admin): void
    {
        if ($template->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => 'Only pending templates can be approved.'
            ]);
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
    }

    public function reject(FormTemplate $template, User $admin, string $reason): void
    {
        if ($template->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'approval_status' => 'Only pending templates can be rejected.'
            ]);
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
}
