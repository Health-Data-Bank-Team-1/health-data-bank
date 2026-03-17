<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;

class UserDashboard extends Component
{
    public function render()
    {
        return view('dashboards.user')
            ->layout('layouts.user');
    }
}
