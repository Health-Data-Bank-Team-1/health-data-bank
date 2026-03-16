<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class DatabaseManagement extends Component
{
    public function render()
    {
        return view('livewire.admin.database-management')
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Database Management'
            ]);
    }
}
