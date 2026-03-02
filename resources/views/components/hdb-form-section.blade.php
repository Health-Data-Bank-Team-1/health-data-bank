@props(['submit'])

<div {{ $attributes->merge(['class' => 'max-w-3xl mx-auto w-full space-y-6']) }}>
    <div class="flex items-center justify-between px-6 py-2 pt-4">
        <h3 class="text-3xl font-medium text-gray-900">
            {{ $title }}
        </h3>

        <button type="button" wire:click="$set('showDescription', true)"
            class="text-sm text-blue-600 hover:text-blue-800 underline">
            View Description
        </button>
    </div>

    <form wire:submit="{{ $submit }}">
        <div class="px-4 py-5 sm:p-6 {{ isset($actions) ? 'border-b' : '' }}">
            <div class="grid grid-cols-6 gap-6 pb-4">
                {{ $form }}
            </div>
        </div>

        @if (isset($actions))
            <div class="flex items-center justify-end space-x-2 px-4 py-3 bg-gray-50 sm:px-6">
                {{ $actions }}
            </div>
        @endif
    </form>

    <x-dialog-modal wire:model="showDescription">
        <x-slot name="title">
            {{ $title }} Description
        </x-slot>

        <x-slot name="content">
            {{ $description }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDescription', false)">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
