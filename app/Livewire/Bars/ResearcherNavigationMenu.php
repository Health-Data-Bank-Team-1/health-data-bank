<?php

namespace App\Livewire\Bars;

use Livewire\Component;

class ResearcherNavigationMenu extends Component
{
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public function render()
    {
        return view('bars.researcher-navigation-menu');
    }
}
