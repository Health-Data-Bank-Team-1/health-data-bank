<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class FormSubmissionController extends Controller
{
    public function store(Request $request)
    {
        // Resolve accounts.uuid from authenticated user
        $user = $request->user();

        $accountId = null;
        if ($user && !empty($user->email)) {
            $accountId = DB::table('accounts')->where('email', $user->email)->value('id');
        }

        if (!$accountId) {
            AuditLogger::log(
                'form_submission_failed',
                'blocked',
                'account_mapping_failed',
                'form_template',
                (string) ($request->input('form_template_id') ?? ''),
                [],
                null
            );

            return response()->json(['message' => 'Account mapping failed.'], 403);
        }

        try {
            $validated = $request->validate([
                'form_template_id' => 'required|exists:form_templates,id',
                'entries' => 'required|array',
                'entries.*.field_id' => 'required',
                'entries.*.value' => 'nullable',
                ]);

            $submission = DB::transaction(function () use ($validated, $accountId) {
                $submission = FormSubmission::create([
                    'account_id' => $accountId, // accounts UUID
                    'form_template_id' => $validated['form_template_id'],
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

                foreach ($validated['entries'] as $entry) {
                    $submission->healthEntries()->create([
                        'field_id' => $entry['field_id'],
                        'value' => $entry['value'] ?? null,
                    ]);
                }

                return $submission;
            });

            AuditLogger::log(
                'form_submission_success',
                'success',
                null,
                'form_submission',
                (string) $submission->id,
                [
                    'form_template_id' => (string) $validated['form_template_id'],
                    'entry_count' => count($validated['entries']),
                ],
                $accountId
            );

            return response()->json([
                'message' => 'Form submitted successfully.',
                'submission_id' => $submission->id,
            ], 201);

        } catch (ValidationException $ve) {

            $failedFields = array_keys($ve->errors());

            AuditLogger::log(
                'form_submission_failed',
                'failure',
                'validation_failed',
                'form_template',
                (string) ($request->input('form_template_id') ?? ''),
                [
                    'failed_fields' => $failedFields,
                    'error_count' => count($failedFields),
                ],
                $accountId
            );

            throw $ve; // keep default Laravel validation response

        } catch (Throwable $e) {
            AuditLogger::log(
                'form_submission_failed_server',
                'failure',
                'server_error',
                'form_template',
                (string) ($request->input('form_template_id') ?? ''),
                [],
                $accountId
            );

            return response()->json(['message' => 'Submission failed.'], 500);
        }
    }
}
