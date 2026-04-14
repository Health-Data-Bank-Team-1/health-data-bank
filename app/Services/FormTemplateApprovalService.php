<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Models\User;
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
            'approval_status' => 'pending',
        ]);

        AuditLogger::log(
            'form_template_submitted',
            ['forms', 'resource:template', 'workflow:approval', 'outcome:success'],
            null,
            $old,
            [
                'template_id' => (string) $template->id,
                'approval_status' => 'pending',
            ]
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
            'rejection_reason' => null,
        ]);

        AuditLogger::log(
            'form_template_approved',
            ['forms', 'resource:template', 'workflow:approval', 'outcome:success'],
            null,
            $old,
            [
                'template_id' => (string) $template->id,
                'approval_status' => 'approved',
            ]
        );

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
            'rejection_reason' => $reason,
        ]);

        AuditLogger::log(
            'form_template_rejected',
            ['forms', 'resource:template', 'workflow:approval', 'outcome:success'],
            null,
            $old,
            [
                'template_id' => (string) $template->id,
                'approval_status' => 'rejected',
                'has_reason' => true,
            ]
        );
    }

    /**
     * Create immutable version snapshot
     */
    private function createVersionSnapshot(FormTemplate $template, string $adminId): void
    {
        $nextVersion = FormTemplateVersion::where('form_template_id', $template->id)->max('version') ?? 0;

        $template->loadMissing('fields');

        $schema = $template->schema;

        if (is_null($schema)) {
            $schema = $template->fields
                ->sortBy('id')
                ->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'label' => $field->label,
                        'metric_key' => $field->metric_key,
                        'field_type' => $field->field_type,
                        'validation_rules' => $field->validation_rules,
                        'goal_enabled' => $field->goal_enabled,
                        'options' => $field->options,
                    ];
                })
                ->values()
                ->toArray();
        }
    }
}
