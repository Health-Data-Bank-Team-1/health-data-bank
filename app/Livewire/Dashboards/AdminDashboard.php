<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;

class AdminDashboard extends Component
{
    public function render()
    {
        return view('dashboards.admin')
            ->layout('layouts.admin');
    }
}
