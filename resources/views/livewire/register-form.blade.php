<form wire:submit="register">
    <div>
        <x-label for="role" value="Role" />
        <select wire:model.live="role" id="role" class="block mt-1 w-full">
            <option value="user">User</option>
            <option value="researcher">Researcher</option>
            <option value="provider">Provider</option>
        </select>
        <x-input-error for="role" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="name" value="Name" />
        <x-input wire:model="name" id="name" class="block mt-1 w-full" type="text" />
        <x-input-error for="name" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="email" value="Email" />
        <x-input wire:model="email" id="email" class="block mt-1 w-full" type="email" />
        <x-input-error for="email" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="date_of_birth" value="Date of Birth" />
        <x-input wire:model="date_of_birth" id="date_of_birth" class="block mt-1 w-full" type="date" />
        <x-input-error for="date_of_birth" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="gender" value="Gender" />
        <select wire:model="gender" id="gender" class="block mt-1 w-full">
            <option value="">Select gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
        </select>
        <x-input-error for="gender" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="password" value="Password" />
        <x-input wire:model="password" id="password" class="block mt-1 w-full" type="password" />
        <x-input-error for="password" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-label for="password_confirmation" value="Confirm Password" />
        <x-input
            wire:model="password_confirmation"
            id="password_confirmation"
            class="block mt-1 w-full"
            type="password"
        />
        <x-input-error for="password_confirmation" class="mt-2" />
    </div>

    @if ($role === 'researcher')
        <div class="mt-4">
            <x-label for="organization" value="Organization" />
            <x-input wire:model="organization" id="organization" type="text" class="block mt-1 w-full" />
            <x-input-error for="organization" class="mt-2" />
        </div>
    @endif

    @if ($role === 'provider')
        <div class="mt-4">
            <x-label for="organization" value="Organization" />
            <x-input wire:model="organization" id="organization" type="text" class="block mt-1 w-full" />
            <x-input-error for="organization" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-label for="license" value="License" />
            <x-input wire:model="license" id="license" type="text" class="block mt-1 w-full" />
            <x-input-error for="license" class="mt-2" />
        </div>
    @endif

    <div class="flex items-center justify-end mt-4">
        <a
            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            href="{{ route('login') }}"
        >
            {{ __('Already registered?') }}
        </a>

        <x-button class="ms-4">
            {{ __('Register') }}
        </x-button>
    </div>
</form>
