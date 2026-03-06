<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ReportReview extends Component
{
    public function render()
    {
        return view('livewire.admin.report-review')
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Report Review'
            ]);
    }
}
