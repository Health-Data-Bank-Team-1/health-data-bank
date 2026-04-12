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
        $patientQuery = Account::query()
            ->where('account_type', 'User')
            ->whereDoesntHave('users.roles', function ($query) {
                $query->whereIn('name', ['provider', 'researcher', 'admin']);
            });

        $totalPatients = (clone $patientQuery)->count();

        $activePatients = (clone $patientQuery)
            ->where('status', 'ACTIVE')
            ->count();

        $deactivatedPatients = (clone $patientQuery)
            ->where('status', 'DEACTIVATED')
            ->count();

        $patientsWithHealthEntries = HealthEntry::query()
            ->join('accounts', 'health_entries.account_id', '=', 'accounts.id')
            ->leftJoin('users', 'users.account_id', '=', 'accounts.id')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                    ->where('model_has_roles.model_type', '=', \App\Models\User::class);
            })
            ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('accounts.account_type', 'User')
            ->where(function ($query) {
                $query->whereNull('roles.name')
                    ->orWhereNotIn('roles.name', ['provider', 'researcher', 'admin']);
            })
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
