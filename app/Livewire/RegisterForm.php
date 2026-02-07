<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class RegisterForm extends Component
{
    public $role = 'User';
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $organization = '';
    public $license = '';

    public function register()
    {
        if ($this->role === 'HealthcareProvider') {
            $validated = $this->validate([
                'role' => 'required',
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'password_confirmation' => 'required',
                'organization' => 'required',
                'license' => 'required'
            ]);
        } else {
            $validated = $this->validate([
                'role' => 'required',
                'name' => 'required',
                'email' => 'required',
                'password' => 'required',
                'password_confirmation' => 'required',
            ]);
        }

        Http::post(route('register', $validated));
    }

    public function render()
    {
        return view('livewire.register-form');
    }
}
