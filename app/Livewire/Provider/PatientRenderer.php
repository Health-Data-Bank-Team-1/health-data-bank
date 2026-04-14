<?php

namespace App\Livewire\Provider;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Notification;
use App\Models\ProviderFeedback;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PatientRenderer extends Component
{
    public $patientAccount;
    public $healthEntries;

    public string $feedback = '';
    public string $recommended_actions = '';

    public array $feedbackEntries = [];

    public function mount($patient)
    {
        $patientAccount = Account::query()
            ->where('id', $patient)
            ->where('account_type', 'User')
            ->first();

        abort_unless($patientAccount, 404, 'Patient not found.');

        $this->patientAccount = $patientAccount;

        $this->healthEntries = HealthEntry::query()
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

        $this->loadFeedback();
    }

    public function submitFeedback(): void
    {
        $this->validate([
            'feedback' => ['required', 'string', 'max:2000'],
            'recommended_actions' => ['nullable', 'string', 'max:2000'],
        ]);

        $providerAccountId = Auth::user()?->account_id;

        abort_unless($providerAccountId, 403, 'Provider account not found.');

        ProviderFeedback::create([
            'patient_account_id' => $this->patientAccount->id,
            'provider_account_id' => $providerAccountId,
            'feedback' => $this->feedback,
            'recommended_actions' => $this->recommended_actions,
        ]);

        Notification::create([
            'account_id' => $this->patientAccount->id,
            'type' => 'provider_feedback',
            'message' => 'Your provider added new feedback and recommended actions to your profile.',
            'status' => 'unread',
        ]);

        AuditLogger::log(
            'provider_feedback_created',
            ['provider', 'resource:patient_feedback', 'outcome:success'],
            null,
            [],
            [
                'patient_id' => $this->patientAccount->id,
                'provider_account_id' => $providerAccountId,
            ]
        );

        session()->flash('message', 'Feedback submitted and patient notified successfully.');

        $this->reset(['feedback', 'recommended_actions']);
        $this->loadFeedback();
    }

    protected function loadFeedback(): void
    {
        $this->feedbackEntries = ProviderFeedback::query()
            ->where('patient_account_id', $this->patientAccount->id)
            ->with(['provider'])
            ->latest()
            ->get()
            ->map(function (ProviderFeedback $entry) {
                return [
                    'id' => $entry->id,
                    'feedback' => $entry->feedback,
                    'recommended_actions' => $entry->recommended_actions,
                    'provider_name' => $entry->provider?->name ?? 'Provider',
                    'created_at' => optional($entry->created_at)?->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.provider.patient-renderer')
            ->layout('layouts.provider');
    }
}
