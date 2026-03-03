<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\HealthEntry;
use App\Services\HealthDataEncryptionService;

class FormSubmissionController extends Controller
{
    public function __construct(
        private HealthDataEncryptionService $encryptionService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_template_id' => 'required|exists:form_templates,id',
            'entries' => 'required|array',
        ]);

        // Get or create an account for the authenticated user
        $account = \App\Models\Account::firstOrCreate(
            ['email' => $request->user()->email],
            [
                'name' => $request->user()->name,
                'account_type' => 'User',
                'status' => 'ACTIVE',
            ]
        );

        // Create form submission
        $submission = FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $validated['form_template_id'],
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        // Create health entries with encrypted values
        foreach ($validated['entries'] as $entry) {
            // Prepare the data to encrypt
            $dataToEncrypt = [
                'field_id' => $entry['field_id'] ?? null,
                'value' => $entry['value'] ?? null,
                'submitted_at' => now()->toIso8601String(),
            ];

            // Create entry with encrypted data
            // The EncryptedArray cast will automatically encrypt before saving
            HealthEntry::create([
                'submission_id' => $submission->id,
                'account_id' => $account->id,
                'timestamp' => now(),
                'encrypted_values' => $dataToEncrypt,  // ← Cast handles encryption
            ]);
        }

        return response()->json([
            'message' => 'Form submitted successfully.',
            'submission_id' => $submission->id,
        ], 201);
    }
}