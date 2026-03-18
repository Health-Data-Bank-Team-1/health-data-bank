<?php

namespace App\Livewire\Bars;

use Livewire\Component;

class ProviderNavigationMenu extends Component
{
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public function render()
    {
        return view('bars.provider-navigation-menu');
    }
}
