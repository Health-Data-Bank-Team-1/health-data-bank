<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PatientRecordController extends Controller
{
    public function show(string $patient): JsonResponse
    {
        $providerAccountId = Auth::user()?->account_id;

        abort_unless($providerAccountId, 403, 'Provider account not found.');

        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->first();

        abort_unless($patientAccount, 404, 'Patient not found.');

        $isLinked = $patientAccount->providers()
            ->where('provider_id', $providerAccountId)
            ->exists();

        abort_unless($isLinked, 403, 'You are not authorized to access this patient record.');

        $entries = HealthEntry::query()
            ->where('account_id', $patientAccount->id)
            ->orderByDesc('timestamp')
            ->get(['id', 'timestamp', 'encrypted_values']);

        AuditLogger::log(
            'provider_patient_record_view',
            ['provider', 'resource:patient_record', 'outcome:success'],
            null,
            [],
            [
                'patient_id' => $patientAccount->id,
                'provider_account_id' => $providerAccountId,
            ]
        );

        return response()->json([
            'patient' => [
                'id' => $patientAccount->id,
                'name' => $patientAccount->name,
                'email' => $patientAccount->email,
                'status' => $patientAccount->status,
            ],
            'health_entries' => $entries,
        ]);
    }
}
