<?php

namespace App\Livewire\Researcher;

use Livewire\Component;

class ResearcherReports extends Component
{
    public function render()
    {
        return view('livewire.researcher.reports')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Reports'
            ]);
    }
}
