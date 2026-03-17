<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;

class ResearcherDashboard extends Component
{
    public function render()
    {
        return view('dashboards.researcher')
            ->layout('layouts.researcher');
    }
}
