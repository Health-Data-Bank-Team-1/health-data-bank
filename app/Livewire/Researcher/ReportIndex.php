<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ReportIndex extends Component
{
    public function render()
    {
        return view('livewire.researcher.report-index', [
            'reports' => Report::all()
        ]);
    }
}
