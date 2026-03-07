<?php

namespace App\Livewire\Bars;

use Livewire\Component;

class AdminNavigationMenu extends Component
{
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public function render()
    {
        return view('bars.admin-navigation-menu');
    }
}
