<?php

namespace App\Livewire\Researcher;

use Livewire\Component;
use App\Models\Report;

class ResearcherReports extends Component
{
    public $currReport = null;

    public function mount(Report $report) {
        $this->currReport = $report;
    }

    public function render()
    {
        return view('livewire.researcher.reports')
            ->layout('layouts.researcher');
    }
}
