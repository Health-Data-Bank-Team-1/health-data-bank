<?php

namespace App\Livewire\Researcher;

use Livewire\Component;

class ResearcherReportGenerator extends Component
{
    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Report Generator'
            ]);
    }
}
