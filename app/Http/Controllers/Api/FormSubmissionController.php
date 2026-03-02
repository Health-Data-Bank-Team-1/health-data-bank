<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\Account;

class FormSubmissionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_template_id' => 'required|exists:form_templates,id',
            'entries' => 'required|array',
        ]);

        // Get or create an account for the authenticated user
        // Since users don't have account_id, we need to create one
        $account = Account::firstOrCreate(
            ['email' => $request->user()->email],
            [
                'name' => $request->user()->name,
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]
        );

        $submission = FormSubmission::create([
            'account_id' => $account->id,  // ← Use the UUID account ID
            'form_template_id' => $validated['form_template_id'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        foreach ($validated['entries'] as $entry) {
            $submission->healthEntries()->create([
                'account_id' => $account->id,
                'timestamp' => now(),
                'encrypted_values' => $entry['value'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Form submitted successfully.',
            'submission_id' => $submission->id,
        ], 201);
    }
}