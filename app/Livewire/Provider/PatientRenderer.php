<?php

namespace App\Livewire\Provider;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\AuditLogger;
use Livewire\Component;

class PatientRenderer extends Component
{
    public $patientAccount;

    public $healthEntries;


    public function mount($patient)
    {
        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->first();

        $this->patientAccount = $patientAccount;

        $healthEntries = HealthEntry::query()
            ->where('account_id', $patientAccount->id)
            ->whereHas('submission', function ($query) {
                $query->whereNull('deleted_at');
            })
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

        $this->healthEntries = $healthEntries;
    }

    public function render()
    {
        return view('livewire.provider.patient-renderer')
            ->layout('layouts.provider');
    }
}
