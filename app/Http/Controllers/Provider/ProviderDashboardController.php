<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\AuditLogger;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $patientQuery = Account::query()->where('account_type', 'User');

        $totalPatients = (clone $patientQuery)->count();

        $activePatients = (clone $patientQuery)
            ->where('status', 'ACTIVE')
            ->count();

        $deactivatedPatients = (clone $patientQuery)
            ->where('status', 'DEACTIVATED')
            ->count();

        $patientsWithHealthEntries = HealthEntry::query()
            ->join('accounts', 'health_entries.account_id', '=', 'accounts.id')
            ->where('accounts.account_type', 'User')
            ->distinct()
            ->count('health_entries.account_id');

        AuditLogger::log(
            'provider_dashboard_view',
            ['provider', 'resource:dashboard']
        );

        return response()->json([
            'totals' => [
                'patients' => $totalPatients,
                'active_patients' => $activePatients,
                'deactivated_patients' => $deactivatedPatients,
                'patients_with_health_entries' => $patientsWithHealthEntries,
            ],
        ]);
    }
}
