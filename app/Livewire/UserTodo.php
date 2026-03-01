<?php

namespace App\Livewire;

use Livewire\Component;

class UserTodo extends Component
{
    public function render()
    {
        return view('livewire.user-todo')
            ->layout('layouts.app');
    }
}
