<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Livewire\Component;

class RegisterForm extends Component
{
    public $role = 'user';
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $date_of_birth = '';
    public $gender = '';
    public $organization = '';
    public $license = '';

    public function register(CreatesNewUsers $creator)
    {
        if ($this->role === 'provider') {
            $validated = $this->validate([
                'role' => ['required', 'string', 'in:user,researcher,provider'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'confirmed'],
                'password_confirmation' => ['required'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', 'in:male,female,other'],
                'organization' => ['required', 'string', 'max:255'],
                'license' => ['required', 'string', 'max:255'],
            ]);
        } elseif ($this->role === 'researcher') {
            $validated = $this->validate([
                'role' => ['required', 'string', 'in:user,researcher,provider'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'confirmed'],
                'password_confirmation' => ['required'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', 'in:male,female,other'],
                'organization' => ['required', 'string', 'max:255'],
            ]);
        } else {
            $validated = $this->validate([
                'role' => ['required', 'string', 'in:user,researcher,provider'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'confirmed'],
                'password_confirmation' => ['required'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['required', 'in:male,female,other'],
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
