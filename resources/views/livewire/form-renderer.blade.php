<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Forms') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <x-hdb-form-section submit="submit">
                <x-slot name="title">
                    {{ $form->title }}
                </x-slot>

                <x-slot name="description">
                    <div class="pt-2 pr-4">{{ $form->description }}</div>
                </x-slot>

                <x-slot name="form">
                    <div class="col-span-6 sm:col-span-4">
                        @foreach ($form->fields as $field)
                            <div class="py-2">
                                <x-label for="{{ $field->id }}" value="{{ $field->label }}" />

                                @if ($field->field_type === 'Text')
                                    <x-input wire:model="entries.{{ $field->id }}" id="{{ $field->id }}"
                                             class="mt-1 block w-full" type="text" />
                                @elseif ($field->field_type === 'RadioButton')
                                    <div class="mt-2 space-y-2">
                                        @foreach ($field->options as $option)
                                            <label class="flex items-center space-x-4 mt-1">
                                                <input type="radio" wire:model="entries.{{ $field->id }}"
                                                       value="{{ $option }}"
                                                       class="h-4 w-4 mr-2 text-indigo-600 border-gray-300 focus:ring-indigo-500 rounded">
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($field->field_type === 'Checkbox')
                                    <div class="mt-2 space-y-2">
                                        @foreach ($field->options as $option)
                                            <label class="flex items-center space-x-4 mt-1">
                                                <input type="checkbox" wire:model="entries.{{ $field->id }}"
                                                       value="{{ $option }}"
                                                       class="h-4 w-4 mr-2 text-indigo-600 border-gray-300 focus:ring-indigo-500 rounded">
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($field->field_type === 'Date')
                                    <x-input wire:model="entries.{{ $field->id }}" id="{{ $field->id }}"
                                             class="mt-1 block w-full" type="date" />
                                @elseif ($field->field_type === 'Number')
                                    <x-input wire:model="entries.{{ $field->id }}" id="{{ $field->id }}"
                                             class="mt-1 block w-full" type="number" />
                                @endif

                                @error("entries.$field->id")
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                    @endforeach
                </x-slot>

                <x-slot name="actions">
                    <div>
                        <div wire:loading="submit" class="mt-2 flex items-center space-x-3">
                            <span class="ml-4 text-sm text-gray-600">Submitting…</span>
                        </div>
                        <x-button wire:loading.attr="disabled">
                            {{ __('Submit') }}
                        </x-button>
                    </div>
                </x-slot>
            </x-hdb-form-section>
        </div>
    </div>
</div>
