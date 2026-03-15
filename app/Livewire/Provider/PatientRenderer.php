<?php

namespace App\Livewire\Provider;

use Livewire\Component;
use App\Models\Account;

class PatientRenderer extends Component
{
    public $patient;

    public function mount($patient)
    {
        $this->patient = $patient;
    }

    public function render()
    {
        return view('livewire.provider.patient-renderer')
            ->layout('layouts.provider');
    }
}
