<?php

namespace App\Livewire\Bars;

use Livewire\Component;

class UserNavigationMenu extends Component
{
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public function render()
    {
        return view('bars.user-navigation-menu');
    }
}
