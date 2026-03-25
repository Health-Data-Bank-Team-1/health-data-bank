<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg grid grid-cols-1 gap-6 p-6">

            <div class="p-4 bg-white rounded shadow border border-gray-100">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Cohort Builder</h2>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <x-label for="name" value="Cohort Name" />
                            <x-input id="name" wire:model="name" type="text" class="w-full" />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <x-label for="purpose" value="Cohort Purpose" />
                            <x-input id="purpose" wire:model="purpose" type="text" class="w-full" />
                            <x-input-error for="purpose" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                        <div class="flex flex-col">
                            <x-label for="min_age" value="Minimum Age" />
                            <input id="min_age" type="number" wire:model="min_age" class="border border-gray-300 rounded px-2 py-1 w-full">
                            <x-input-error for="min_age" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <x-label for="max_age" value="Maximum Age" />
                            <input id="max_age" type="number" wire:model="max_age" class="border border-gray-300 rounded px-2 py-1 w-full">
                            <x-input-error for="max_age" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <x-label for="gender" value="Gender" />
                            <select id="gender" wire:model="gender" class="border border-gray-300 rounded px-2 py-1 w-full">
                                <option value=""></option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <x-button wire:click="store" class="w-full sm:w-auto justify-center">
                        Generate Cohort
                    </x-button>
                </div>

                @error('cohort')
                    <div class="text-red-600 mt-2 text-sm">{{ $message }}</div>
                @enderror

                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="mt-4 bg-green-100 text-green-800 p-3 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <div class="p-4 bg-white rounded shadow border border-gray-100">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Report Generator</h2>

                <div class="space-y-4">
                    <div class="flex flex-col">
                        <x-label for="selectedCohort" value="Select Cohort" />
                        <select id="selectedCohort" wire:model="selectedCohort" class="border border-gray-300 rounded px-2 py-1 w-full">
                            <option value="">Choose a cohort...</option>
                            @foreach ($this->cohorts as $cohort)
                                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="selectedCohort" class="mt-2" />
                    </div>

                    <div class="flex flex-col">
                        <x-label for="keys" value="Select Keys (Comma separated)" />
                        <x-input id="keys" wire:model="keys" type="text" class="w-full" />
                        <x-input-error for="keys" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <x-label for="from" value="Start Date" />
                            <x-input id="from" wire:model="from" type="date" class="w-full" />
                            <x-input-error for="from" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <x-label for="to" value="End Date" />
                            <x-input id="to" wire:model="to" type="date" class="w-full" />
                            <x-input-error for="to" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <x-button wire:click="generateReport" class="w-full sm:w-auto justify-center">
                        Generate Report
                    </x-button>
                </div>
                @error('report')
                    <div class="text-red-600 mt-2 text-sm">{{ $message }}</div>
                @enderror

                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="mt-4 bg-green-100 text-green-800 p-3 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
