<form wire:submit="register">
    <div>
        <x-label for="role" value="{{ __('Role (required)') }}" />
        <select wire:model.live="role" id="role" class="block mt-1 w-full">
            <option value="User">User</option>
            <option value="HealthcareProvider">Healthcare Provider</option>
            <option value="Researcher">Researcher</option>
        </select>
        <div>
            @error('role')
                <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div>
        <x-label for="name" value="{{ __('Name (required)') }}" />
        <x-input wire:model="name" id="name" class="block mt-1 w-full" type="text" />
        <div>
            @error('name')
                <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <x-label for="email" value="{{ __('Email (required)') }}" />
        <x-input wire:model="email" id="email" class="block mt-1 w-full" type="email" />
        <div>
            @error('email')
                <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <x-label for="password" value="{{ __('Password (required)') }}" />
        <x-input wire:model="password" id="password" class="block mt-1 w-full" type="password" />
        <div>
            @error('password')
                <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <x-label for="password_confirmation" value="{{ __('Confirm Password (required)') }}" />
        <x-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
            type="password" />
        <div>
            @error('password_confirmation')
                <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    @if ($role === 'HealthcareProvider')
        <div class="mt-4">
            <x-label for="organization" value="{{ __('Organization (required)')}}" />
            <x-input wire:model="organization" id="organization" type="text" class="block mt-1 w-full" />
            <div>
                @error('organization')
                    <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="mt-4">
            <x-label for="license" value="{{ __('License (required)')}}" />
            <x-input wire:model="license" id="license" type="text" class="block mt-1 w-full" />
            <div>
                @error('license')
                    <span class="text-sm text-red-600 mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>
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
