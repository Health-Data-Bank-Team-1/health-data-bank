<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Generate Anonymous Report</h2>
            <p class="text-sm text-gray-600 mb-6">
                Select metrics, date range, and allowed demographic filters to generate an anonymous aggregated report.
            </p>

            @if ($reportMessage)
                <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-3">
                    {{ $reportMessage }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

            <div class="mt-6 flex gap-3">
                <button
                    wire:click="estimatePopulation"
                    type="button"
                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                >
                    Estimate Matching Size
                </button>

                <button
                    wire:click="generateReport"
                    type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Generate Report
                </button>
            </div>

            @if (!is_null($estimatedSize))
                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-sm text-gray-700">
                        Estimated matching population:
                        <span class="font-semibold">{{ $estimatedSize }}</span>
                    </p>
                </div>
            @endif
        </div>

        @if (!empty($summaryStats))
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

        @if (!empty($reportResults))
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

    </div>
</div>
