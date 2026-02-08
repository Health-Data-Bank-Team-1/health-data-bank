<?php

namespace App\Livewire;

use Livewire\Component;

class MyProgress extends Component
{
    public function render()
    {
        return view('livewire.my-progress')
            ->layout('layouts.app');
    }
}
