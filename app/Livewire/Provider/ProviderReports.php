<?php

namespace App\Livewire\Provider;

use Livewire\Component;

class ProviderReports extends Component
{
    public function render()
    {
        return view('livewire.provider.reports')
            ->layout('layouts.provider')
            ->layoutData([
                'header' => 'Reports'
            ]);
    }
}
