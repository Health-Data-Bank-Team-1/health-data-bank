<?php

namespace App\Livewire\Profiles;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ResearcherProfile extends Component
{
    public $user;

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('profiles.researcher-profile', [
            'user' => $this->user,
        ])->layout('layouts.researcher');
    }
}
