<?php

namespace App\Livewire\Provider;

use Livewire\Component;
use App\Models\Account;

class PatientIndex extends Component
{
    public $patients;

    public function mount()
    {
        $user = auth()->user();
        $this->patients = Account::find($user->account_id)->patients;
    }

    public function render()
    {
        return view('livewire.provider.patient-index');
    }
}
