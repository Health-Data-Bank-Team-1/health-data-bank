<?php

namespace App\Livewire\Profiles;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProviderProfile extends Component
{
    public $user;

    public function mount()
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('profiles.provider-profile', [
            'user' => $this->user,
        ])->layout('layouts.provider');
    }
}
