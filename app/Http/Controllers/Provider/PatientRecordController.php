<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PatientRecordController extends Controller
{
    public function show(Request $request, string $patient)
    {
        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->first();

        if (!$patientAccount) {
            return response()->json([
                'message' => 'Patient not found.',
            ], 404);
        }

        $healthEntries = HealthEntry::query()
            ->where('account_id', $patientAccount->id)
            ->orderByDesc('timestamp')
            ->get(['id', 'timestamp', 'encrypted_values']);

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
        ]);
    }
}
