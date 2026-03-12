<?php

namespace App\Livewire\Provider;

use Livewire\Component;

class ProviderPatients extends Component
{
    public function render()
    {
        return view('livewire.provider.patients')
            ->layout('layouts.provider')
            ->layoutData([
                'header' => 'Patients'
            ]);
    }
}
