<?php

namespace App\Services;

use App\Models\FormTemplate;
use App\Services\AuditLogger;

class FormTemplateVersioningService
{
    public function createNewVersion(FormTemplate $template): FormTemplate
    {
        //only approved templates create versions
        if ($template->approval_status !== 'approved') {
            return $template;
        }

        $new = $template->replicate();

        $new->version = $template->version + 1;
        $new->approval_status = 'draft';
        $new->approved_by = null;
        $new->approved_at = null;
        $new->rejection_reason = null;

        $new->push();

        AuditLogger::log(
            'form_template_version_created',
            ['form', 'versioning'],
            $new,
            ['from_version' => $template->version],
            ['to_version' => $new->version]
        );

        return $new;
    }
}
