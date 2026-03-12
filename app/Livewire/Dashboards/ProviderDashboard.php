<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;

class ProviderDashboard extends Component
{
    public function render()
    {
        return view('dashboards.provider')
            ->layout('layouts.provider');
    }
}
