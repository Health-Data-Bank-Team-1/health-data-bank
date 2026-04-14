<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Patients') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Attach Patient</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Assign an existing user account to this provider.
                    </p>

                    <div class="mt-4 flex flex-col md:flex-row gap-3">
                        <select
                            wire:model="selectedPatientId"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >
                            <option value="">Select a patient</option>
                            @foreach($availablePatients as $patient)
                                <option value="{{ $patient['id'] }}">{{ $patient['label'] }}</option>
                            @endforeach
                        </select>

                        <button
                            wire:click="attachPatient"
                            type="button"
                            class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                        >
                            Attach Patient
                        </button>
                    </div>

                    @error('selectedPatientId')
                    <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                    @enderror

                    @if (session()->has('message'))
                        <div class="mt-3 bg-green-100 text-green-800 p-3 rounded text-sm">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>

                <x-section-border />

                <div>
                    <div class="flex justify-center px-4 py-2 mt-4">
                        <x-label for="search" value="Search" class="mt-4 mr-2 block" />
                        <x-input
                            id="search"
                            class="mt-1 block w-full"
                            wire:model="search"
                            placeholder="Search patients..."
                        />
                        <x-button wire:click="getPatients()" class="ml-2">Search</x-button>
                    </div>

                    @if (!empty($found))
                        <ul class="divide-y divide-gray-200 flex-1 overflow-y-auto">
                            @foreach ($found as $acc)
                                <li class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition">
                                    <a
                                        href="{{ route('provider.patients.show', $acc) }}"
                                        class="flex items-center justify-between flex-1 focus:outline-none focus:bg-gray-50"
                                    >
                                        <span class="text-gray-900 font-medium">{{ $acc->name }}</span>
                                        <svg class="h-5 w-5 text-gray-400 ml-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>

                                    <button
                                        wire:click="detachPatient('{{ $acc->id }}')"
                                        onclick="return confirm('Remove this patient from the provider list?')"
                                        type="button"
                                        class="ml-4 px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                    >
                                        Remove
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-6 py-4 text-sm text-gray-500">
                            No patients found.
                        </div>
                    @endif

                    <x-section-border />
                </div>

                <div>
                    <livewire:provider.patient-index />
                </div>
            </div>
        </div>
    </div>
</div>
