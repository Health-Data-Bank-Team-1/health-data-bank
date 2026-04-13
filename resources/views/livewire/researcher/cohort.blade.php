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
                            <option value="other">Other</option>
                        </select>
                        <x-input-error for="gender" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <button
                    wire:click="estimateSize"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Estimate Cohort Size
                </button>

                <button
                    wire:click="saveCohort"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Save Cohort
                </button>
            </div>

            @if(!is_null($estimatedSize))
                <div class="mt-4 rounded bg-blue-50 text-blue-800 p-3 text-sm">
                    Estimated Cohort Size: <strong>{{ $estimatedSize }}</strong>
                </div>
            @endif

            @if (session()->has('message'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 4000)"
                    class="mt-4 bg-green-100 text-green-800 p-3 rounded text-sm"
                >
                    {{ session('message') }}
                </div>
            @endif

            <div class="mt-8 border-t pt-6">
                <h3 class="text-md font-semibold text-gray-800 mb-4">Saved Cohorts</h3>

                @if(count($savedCohorts))
                    <div class="space-y-3">
                        @foreach($savedCohorts as $cohort)
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $cohort['name'] }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $cohort['purpose'] }}</p>
                                    </div>

                                    <div class="text-sm text-gray-500 text-right">
                                        <p>v{{ $cohort['version'] }}</p>
                                        <p>{{ $cohort['created_at'] }}</p>

                                        <button
                                            wire:click="deleteCohort('{{ $cohort['id'] }}')"
                                            wire:confirm="Are you sure you want to delete this cohort?"
                                            type="button"
                                            class="mt-3 inline-flex px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-3 text-sm text-gray-700">
                                    Estimated Size:
                                    <strong>{{ $cohort['estimated_size'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                        No cohorts have been saved yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
