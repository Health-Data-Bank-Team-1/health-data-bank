<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 bg-white rounded shadow">

                <h2 class="text-lg font-semibold mb-4">Cohort Builder</h2>

                <div class="space-y-3">
                    <div class="flex space-x-2 mb-4">
                        <div>
                            <x-label for="name" value="Cohort Name" />
                            <x-input id="name" wire:model="name" type="text" />
                            <x-input-error for="name" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="purpose" value="Cohort Purpose" />
                            <x-input id="purpose" wire:model="purpose" type="text" />
                            <x-input-error for="purpose" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex flex-col items-start mb-2">

                        <x-label for="min_age" value="Minimum Age" />
                        <input id="min_age"type="number" wire:model="min_age" class="border rounded px-2 py-1 w-24">
                        <x-input-error for="min_age" class="mt-2" />
                        <x-label for="max_age" value="Maximum Age" />
                        <input id="max_age" type="number" wire:model="max_age" class="border rounded px-2 py-1 w-24">
                        <x-input-error for="max_age" class="mt-2" />
                        <x-label for="gender" value="Gender" />
                        <select id="gender" wire:model="gender" class="border rounded px-2 py-1">
                            <option value="">{{ '' }}</option>
                            <option value="male">{{ 'Male' }}</option>
                            <option value="female">{{ 'Female' }}</option>
                        </select>

                    </div>

                </div>

                <div class="flex space-x-3 mt-4">

                    <x-button wire:click="store">
                        Generate Cohort
                    </x-button>

                </div>
                @error('cohort')
                    <div class="text-red-600">
                        {{ $message }}
                    </div>
                @enderror
                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="bg-green-100 text-green-800 p-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

            </div>

            <div class="p-4 bg-white rounded shadow">
                <h2 class="text-lg font-semibold mb-4">Report Generator</h2>
                <div class="flex space-x-2 mb-4">
                    <div>
                        <x-label for="selectedCohort" value="Select Cohort" />
                        <select id="selectedCohort" wire:model="selectedCohort" class="border rounded px-2 py-1">
                            @foreach ($this->cohorts as $cohort)
                                <option value="$cohort->id">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="selectedCohort" class="mt-2" />
                    </div>
                </div>
                <div class="flex space-x-2 mb-4">
                    <div>
                        <x-label for="keys" value="Select Keys (Comma separated)" />
                        <x-input id="keys" wire:model="keys" type="text" />
                        <x-input-error for="keys" class="mt-2" />
                    </div>
                </div>
                <div class="flex space-x-2 mb-4">
                    <div>
                        <x-label for="from" value="Start Date" />
                        <x-input id="from" wire:model="from" type="date" />
                        <x-input-error for="from" class="mt-2" />
                    </div>
                    <div>
                        <x-label for="to" value="End Date" />
                        <x-input id="to" wire:model="to" type="date" />
                        <x-input-error for="to" class="mt-2" />
                    </div>
                </div>
                <div class="flex space-x-3 mt-4">

                    <x-button wire:click="generateReport">
                        Generate Report
                    </x-button>

                </div>
                @error('report')
                    <div class="text-red-600">
                        {{ $message }}
                    </div>
                @enderror
                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="bg-green-100 text-green-800 p-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
