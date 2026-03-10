<?php

namespace App\Livewire\Researcher;

use Livewire\Component;

class ResearcherReportGenerator extends Component
{
    public $filters = [];

    public function mount()
    {
        $this->filters = [
            ['field' => '', 'operator' => '', 'value' => '']
        ];
    }

    public function addFilter()
    {
        $this->filters[] = ['field' => '', 'operator' => '', 'value' => ''];
    }

    public function removeFilter($index)
    {
        unset($this->filters[$index]);
        $this->filters = array_values($this->filters);
    }

    public function generateCohort()
    {
        $this->dispatch('cohortGenerated', filters: $this->filters);
    }

    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Report Generator'
            ]);
    }
}
