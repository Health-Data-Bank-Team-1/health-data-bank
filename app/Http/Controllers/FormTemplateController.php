<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormTemplateRequest;
use App\Http\Requests\UpdateFormTemplateRequest;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use App\Services\AuditLogger;

class FormTemplateController extends Controller
{
    public function store(StoreFormTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = FormTemplate::create([
            'title' => $validated['title'],
            'schema' => $validated['schema'],
            'description' => $validated['description'] ?? null,
            'version' => 1,
            'approval_status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);
        AuditLogger::log(
            'form_template_created',
            ['forms', 'resource:template', 'outcome:success'],
            null,
            [],
            [
                'template_id' => (string) $template->id,
                'version' => $template->version,
                'approval_status' => $template->approval_status,
            ]
        );

        return response()->json($template, 201);
    }

    public function update(UpdateFormTemplateRequest $request, FormTemplate $template)
    {
        $approvalReset = false;
        $previousStatus = $template->approval_status;

        if (in_array($template->approval_status, ['approved', 'rejected'], true)) {
            $template->update([
                'approval_status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ]);
            $approvalReset = true;
        }

        $validated = $request->validated();
        $template->update($validated);

        AuditLogger::log(
            'form_template_updated',
            ['forms', 'resource:template', 'outcome:success'],
            null,
            [],
            [
                'template_id' => (string) $template->id,
                'version' => $template->version,
                'title_changed' => array_key_exists('title', $validated),
                'schema_changed' => array_key_exists('schema', $validated),
                'description_changed' => array_key_exists('description', $validated),
                'approval_reset_to_draft' => $approvalReset,
                'previous_approval_status' => $previousStatus,
                'current_approval_status' => $template->approval_status,
            ]
        );

        return response()->json($template);
    }
}