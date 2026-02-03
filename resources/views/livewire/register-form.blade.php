<form wire:submit="register">

    <x-validation-errors class="mb-4" />

    <div>
        <x-label for="role" value="{{ __('Role') }}" />
        <select wire:model.live="role" id="role" class="block mt-1 w-full">
            <option value="User">User</option>
            <option value="Admin">Admin</option>
            <option value="HealthcareProvider">Healthcare Provider</option>
            <option value="Researcher">Researcher</option>
        </select>
    </div>

    <div>
        <x-label for="name" value="{{ __('Name') }}" />
        <x-input wire:model="name" id="name" class="block mt-1 w-full" type="text" />
    </div>

    <div class="mt-4">
        <x-label for="email" value="{{ __('Email') }}" />
        <x-input wire:model="email" id="email" class="block mt-1 w-full" type="email" />
    </div>

    <div class="mt-4">
        <x-label for="password" value="{{ __('Password') }}" />
        <x-input wire:model="password" id="password" class="block mt-1 w-full" type="password" />
    </div>

    <div class="mt-4">
        <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
        <x-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" />
    </div>

    @if ($role === 'Admin')
        <div class="mt-4">
            <x-label for="admin_code" value="Admin Code" />
            <x-input wire:model="admin_code" id="admin_code" type="text" class="block mt-1 w-full" />
        </div>
    @elseif($role === 'Researcher')
        <x-label for="department" value="Department" />
        <x-input wire:model="department" id="department" type="text" class="block mt-1 w-full" />
    @elseif($role === 'HealthcareProvider')
        <x-label for="department" value="Department" />
        <x-input wire:model="department" id="department" type="text" class="block mt-1 w-full" />
    @endif
    <div class="flex items-center justify-end mt-4">
        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            href="{{ route('login') }}">
            {{ __('Already registered?') }}
        </a>

        <x-button class="ms-4">
            {{ __('Register') }}
        </x-button>
    </div>
</form>
