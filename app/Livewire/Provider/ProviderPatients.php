<?php

namespace App\Livewire\Provider;

use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProviderPatients extends Component
{
    public $search = '';
    public $patients;
    public $found = [];

    public ?string $selectedPatientId = null;
    public array $availablePatients = [];

    public function mount()
    {
        $this->loadPatients();
        $this->loadAvailablePatients();
    }

    protected function providerAccount(): ?Account
    {
        $user = Auth::user();

        return $user ? Account::find($user->account_id) : null;
    }

    protected function loadPatients(): void
    {
        $providerAccount = $this->providerAccount();

        $this->patients = $providerAccount?->patients()->orderBy('name')->get() ?? collect();
        $this->found = $this->patients->all();
    }

    protected function loadAvailablePatients(): void
    {
        $providerAccount = $this->providerAccount();

        if (!$providerAccount) {
            $this->availablePatients = [];
            return;
        }

        $attachedIds = $providerAccount->patients()->pluck('accounts.id')->toArray();

        $this->availablePatients = Account::query()
            ->where('account_type', 'User')
            ->whereNotIn('id', $attachedIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'label' => $account->name . ' (' . $account->email . ')',
            ])
            ->toArray();
    }

    public function getPatients()
    {
        if (empty(trim($this->search))) {
            $this->found = $this->patients->all();
            return;
        }

        $term = trim($this->search);

        $this->found = $this->patients
            ->filter(function ($patient) use ($term) {
                return str_contains(strtolower($patient->name), strtolower($term))
                    || str_contains(strtolower($patient->email ?? ''), strtolower($term));
            })
            ->values()
            ->all();
    }

    public function attachPatient(): void
    {
        $this->validate([
            'selectedPatientId' => ['required', 'string', 'exists:accounts,id'],
        ]);

        $providerAccount = $this->providerAccount();

        abort_unless($providerAccount, 403, 'Provider account not found.');

        $providerAccount->patients()->syncWithoutDetaching([$this->selectedPatientId]);

        $this->selectedPatientId = null;

        $this->loadPatients();
        $this->loadAvailablePatients();

        session()->flash('message', 'Patient attached successfully.');
    }

    public function detachPatient(string $patientId): void
    {
        $providerAccount = $this->providerAccount();

        abort_unless($providerAccount, 403, 'Provider account not found.');

        $providerAccount->patients()->detach($patientId);

        $this->loadPatients();
        $this->loadAvailablePatients();

        session()->flash('message', 'Patient removed successfully.');
    }

    public function render()
    {
        return view('livewire.provider.patients')
            ->layout('layouts.provider');
    }


}
