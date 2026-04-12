<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\ProviderFeedback;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PatientRecordController extends Controller
{
    public function show(Request $request, string $patient)
    {
        $providerAccountId = $request->user()?->account_id;
        if (!$providerAccountId) {
            return response()->json([
                'message' => 'Provider account not found.',
            ], 403);
        }

        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->first();

        if (!$patientAccount) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $isLinkedPatient = Account::query()
            ->where('id', $providerAccountId)
            ->whereHas('patients', function ($query) use ($patientAccount) {
                $query->where('accounts.id', $patientAccount->id);
            })
            ->exists();

        if (!$isLinkedPatient) {
            return response()->json([
                'message' => 'You are not authorized to access this patient record.',
            ], 403);
        }

        $healthEntries = HealthEntry::query()
            ->where('account_id', $patientAccount->id)
            ->orderByDesc('timestamp')
            ->get(['id', 'timestamp', 'encrypted_values']);

        $feedback = ProviderFeedback::query()
            ->with(['provider:id,name'])
            ->where('patient_account_id', $patientAccount->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (ProviderFeedback $item) {
                return [
                    'id' => $item->id,
                    'feedback' => $item->feedback,
                    'recommended_actions' => $item->recommended_actions,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'provider' => [
                        'id' => $item->provider?->id,
                        'name' => $item->provider?->name,
                    ],
                ];
            });

        AuditLogger::log(
            'provider_patient_record_view',
            ['provider', 'resource:patient_record'],
            null,
            [],
            [
                'patient_id' => $patientAccount->id,
            ]
        );

        return response()->json([
            'patient' => [
                'id' => $patientAccount->id,
                'name' => $patientAccount->name,
                'email' => $patientAccount->email,
                'status' => $patientAccount->status,
                'account_type' => $patientAccount->account_type,
            ],
            'health_entries' => $healthEntries,
            'feedback' => $feedback,
        ]);
    }
}