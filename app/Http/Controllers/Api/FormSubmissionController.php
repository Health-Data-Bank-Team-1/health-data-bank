<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormSubmissionRequest;
use App\Models\FormSubmission;
use App\Models\FormField;
use App\Models\HealthEntry;
use App\Services\HealthDataEncryptionService;
use App\Services\SubmissionFlaggingService;
use Illuminate\Support\Str;

class FormSubmissionController extends Controller
{
    private HealthDataEncryptionService $encryptionService;

    public function __construct(HealthDataEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
        $this->middleware('auth:sanctum');
    }

    public function store(StoreFormSubmissionRequest $request)
    {
        $validated = $request->validated();

        $user = $request->user();
        if (! $user || ! $user->account_id) {
            return response()->json([
                'message' => 'User is not linked to an account.',
            ], 422);
        }

        $submission = FormSubmission::create([
            'id' => Str::uuid(),
            'account_id' => $user->account_id,
            'form_template_id' => $validated['form_template_id'],
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        foreach ($validated['entries'] as $entry) {
            $field = FormField::findOrFail($entry['field_id']);

            $encryptedData = [
                'field_id' => $entry['field_id'],
                'metric_key' => $field->metric_key,
                'field_label' => $field->label,
                'field_type' => $field->field_type,
                'value' => $entry['value'] ?? null,
            ];

            HealthEntry::create([
                'id' => Str::uuid(),
                'submission_id' => $submission->id,
                'account_id' => $user->account_id,
                'timestamp' => now(),
                'encrypted_values' => $encryptedData,
            ]);
        }

        app(SubmissionFlaggingService::class)->evaluate($submission);

        return response()->json([
            'message' => 'Form submitted successfully.',
            'submission_id' => $submission->id,
        ], 201);
    }
}
