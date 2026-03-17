<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 bg-white rounded shadow">

                <h2 class="text-lg font-semibold mb-4">Cohort Builder</h2>

                <div class="space-y-3">

                    @foreach ($filters as $index => $filter)
                        <div class="flex space-x-2 items-center">

                            <x-label for="filters-{{ $index }}-field" value="Field" />
                            <select wire:model="filters.{{ $index }}.field" class="border rounded px-2 py-1">
                                <option value="">{{ '' }}</option>
                                <option value="weight">Weight</option>
                                <option value="heart_rate">Heart Rate</option>
                                <option value="days_slept">Days Slept</option>
                                <option value="meals_ate">Meals Ate</option>
                            </select>

                            <x-label for="filters-{{ $index }}-operator" value="Operator" />
                            <select id="filters-{{ $index }}-operator" wire:model="filters.{{ $index }}.operator" class="border rounded px-2 py-1">
                                <option value="">{{ '' }}</option>
                                <option value=">">{{ '>' }}</option>
                                <option value="<"> {{ '<' }}</option>
                                <option value="=">{{ '=' }}</option>
                                <option value=">=">>{{ '=' }}</option>
                                <option value="<="> {{ '<=' }}</option>
                            </select>

                            <x-label for="filter-value-{{ $index }}" value="Value" />
                            <input id="filter-value-{{ $index }}"type="number" wire:model="filters.{{ $index }}.value"
                                class="border rounded px-2 py-1 w-24">

                            <button wire:click="removeFilter({{ $index }})" class="text-red-600">
                                ✕
                            </button>

                        </div>
                    @endforeach

                </div>

                <div class="flex space-x-3 mt-4">

                    <x-button wire:click="addFilter">
                        + Add Filter
                    </x-button>

                    <x-button wire:click="generateCohort">
                        Generate Cohort
                    </x-button>

                </div>

            </div>
        </div>
    </div>
</div>
