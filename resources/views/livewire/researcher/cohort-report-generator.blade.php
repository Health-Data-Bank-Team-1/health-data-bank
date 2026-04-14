<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Cohort Report Generator</h2>

            @if ($reportMessage)
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 4000)"
                    class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-3"
                >
                    {{ $reportMessage }}
                </div>
            @endif

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select a Saved Cohort</label>
                    <select wire:model.live="selectedCohortId" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">— Create new cohort —</option>
                        @foreach ($savedCohorts as $cohort)
                            <option value="{{ $cohort['id'] }}">{{ $cohort['name'] }} (v{{ $cohort['version'] }}, est. {{ $cohort['estimated_size'] }})</option>
                        @endforeach
                    </select>
                </div>

                @if (! $selectedCohortId)
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-md font-semibold text-gray-800 mb-3">Create New Cohort</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cohort Name</label>
                                <input type="text" wire:model="name" class="w-full border rounded-lg px-3 py-2">
                                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cohort Purpose</label>
                                <input type="text" wire:model="purpose" class="w-full border rounded-lg px-3 py-2">
                                @error('purpose') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Demographic Filters</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Age</label>
                            <input type="number" wire:model="min_age" class="w-full border rounded-lg px-3 py-2">
                            @error('min_age') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Age</label>
                            <input type="number" wire:model="max_age" class="w-full border rounded-lg px-3 py-2">
                            @error('max_age') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select wire:model="gender" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Any</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-md font-semibold text-gray-800 mb-3">Report Parameters</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metrics</label>
                            <input
                                type="text"
                                wire:model="metricsInput"
                                placeholder="e.g. heart_rate, sleep_hours"
                                class="w-full border rounded-lg px-3 py-2"
                            >
                            <p class="text-xs text-gray-500 mt-1">Separate multiple metrics with commas.</p>
                            @error('metricsInput') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input type="date" wire:model="from" class="w-full border rounded-lg px-3 py-2">
                            @error('from') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input type="date" wire:model="to" class="w-full border rounded-lg px-3 py-2">
                            @error('to') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button
                    wire:click="estimatePopulation"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Estimate Matching Size
                </button>

                @if (! $selectedCohortId)
                    <button
                        wire:click="saveCohort"
                        type="button"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                    >
                        Save as Cohort
                    </button>
                @endif

                <button
                    wire:click="generateReport"
                    type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Generate Report
                </button>
            </div>

            @if (! is_null($estimatedSize))
                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-sm text-gray-700">
                        Estimated matching population:
                        <span class="font-semibold">{{ $estimatedSize }}</span>
                    </p>
                </div>
            @endif
        </div>

        @if (! empty($summaryStats))
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Summary</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div><strong>Population Size:</strong> {{ $summaryStats['population_size'] }}</div>
                    <div><strong>From:</strong> {{ $summaryStats['from'] }}</div>
                    <div><strong>To:</strong> {{ $summaryStats['to'] }}</div>
                    <div class="md:col-span-2">
                        <strong>Metrics:</strong> {{ implode(', ', $summaryStats['metrics']) }}
                    </div>
                </div>
            </div>
        @endif

        @if (! empty($reportResults))
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aggregated Results</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left border-b">Metric</th>
                            <th class="px-4 py-2 text-left border-b">Value</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($reportResults as $metric => $value)
                            <tr>
                                <td class="px-4 py-2 border-b">{{ $metric }}</td>
                                <td class="px-4 py-2 border-b">
                                    @if (is_array($value))
                                        <pre class="whitespace-pre-wrap text-xs">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (! empty($timeseriesResults))
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeseries Results</h3>

                @foreach ($timeseriesResults as $ts)
                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">
                            {{ $ts['metric'] }} <span class="text-sm text-gray-500">(bucket: {{ $ts['bucket'] }})</span>
                        </h4>

                        @if (! empty($ts['points']))
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left border-b">Bucket Start</th>
                                        <th class="px-4 py-2 text-left border-b">Count</th>
                                        <th class="px-4 py-2 text-left border-b">Min</th>
                                        <th class="px-4 py-2 text-left border-b">Max</th>
                                        <th class="px-4 py-2 text-left border-b">Avg</th>
                                        <th class="px-4 py-2 text-left border-b">Latest</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($ts['points'] as $point)
                                        <tr>
                                            <td class="px-4 py-2 border-b">{{ $point['bucket_start'] }}</td>
                                            <td class="px-4 py-2 border-b">{{ $point['count'] ?? 0 }}</td>
                                            <td class="px-4 py-2 border-b">{{ $point['min'] ?? '-' }}</td>
                                            <td class="px-4 py-2 border-b">{{ $point['max'] ?? '-' }}</td>
                                            <td class="px-4 py-2 border-b">{{ is_null($point['avg'] ?? null) ? '-' : number_format($point['avg'], 2) }}</td>
                                            <td class="px-4 py-2 border-b">{{ $point['latest'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No data points for this metric.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Saved Cohorts</h3>

            @if (count($savedCohorts))
                <div class="space-y-3">
                    @foreach ($savedCohorts as $cohort)
                        <div class="rounded-lg border border-gray-200 p-4 {{ $selectedCohortId === $cohort['id'] ? 'ring-2 ring-blue-500' : '' }}">
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
