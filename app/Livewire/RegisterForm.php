<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;

class RegisterForm extends Component
{
    public $role = 'User';

    #[Validate('required')]
    public $name = '';

    #[Validate('required')]
    public $email = '';

    #[Validate('required')]
    public $password = '';

    #[Validate('required')]
    public $password_confirmation = '';

    public $admin_code = '';
    public $department = '';

    public function render()
    {
        return view('livewire.register-form');
    }

    public function register()
    {
        $this->validate();
    }
}
