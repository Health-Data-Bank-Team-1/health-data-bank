<?php

namespace App\Livewire;

use Livewire\Component;

class UserFormSelect extends Component
{
    public function render()
    {
        return view('livewire.user-form-select')
            ->layout('layouts.app');
    }
}
