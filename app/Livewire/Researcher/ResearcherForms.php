<?php

namespace App\Livewire\Researcher;

use Livewire\Component;

class ResearcherForms extends Component
{
    public function render()
    {
        return view('livewire.researcher.forms')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Forms'
            ]);
    }
}
