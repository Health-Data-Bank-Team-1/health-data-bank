<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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
                    <button
                        wire:click="estimateSize"
                        type="button"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                    >
                        Estimate Cohort Size
                    </button>

                    <button wire:click="saveCohort" type="button" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                        Save Cohort
                    </button>
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
    </div>
</div>
