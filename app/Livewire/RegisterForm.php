<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Auth;

class RegisterForm extends Component
{
    public $role = 'User';
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $organization = '';
    public $license = '';

    public function register(CreatesNewUsers $creator)
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

        $user = $creator->create($validated);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.register-form');
    }
}
