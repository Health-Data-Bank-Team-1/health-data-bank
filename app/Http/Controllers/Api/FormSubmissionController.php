<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\AuditLog;

class FormSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_template_id' => 'required|exists:form_templates,id',
            'entries' => 'required|array',
        ]);

        $submission = FormSubmission::create([
            'account_id' => auth()->id(),
            'form_template_id' => $validated['form_template_id'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        foreach ($validated['entries'] as $entry) {
            $submission->healthEntries()->create([
                'field_id' => $entry['field_id'],
                'value' => $entry['value'],
            ]);
        }

        return response()->json([
            'message' => 'Form submitted successfully.',
            'submission_id' => $submission->id,
        ], 201);
    }
}