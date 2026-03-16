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

    public function mount()
    {
        $user = Auth::user();
        $this->patients = Account::find($user->account_id)->patients;
    }

    public function getPatients()
    {
        if (empty(trim($this->search))) {
            $this->found = $this->patients;
            return;
        }

        foreach ($this->patients as $patient) {
            if (strcasecmp($patient->name, $this->search) == 0) {
                array_push($this->found, $patient);
            }
        }

        return;
    }

    public function render()
    {
        return view('livewire.provider.patients')
            ->layout('layouts.provider')
            ->layoutData([
                'header' => 'Patients'
            ]);
    }
}
