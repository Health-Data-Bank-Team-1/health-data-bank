<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use App\Models\Account;

class ProviderDashboard extends Component
{
    public function render()
    {
        return view('dashboards.provider')
            ->layout('layouts.provider');
    }
}
