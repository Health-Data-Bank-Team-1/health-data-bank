<div>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Reports
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc ml-6">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form wire:submit.prevent="generateReport" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                        <select wire:model.live="mode" class="w-full border-gray-300 rounded-md">
                            <option value="participants">Selected Participants</option>
                            <option value="group">Group Analytics</option>
                        </select>
                    </div>

                    @if ($mode === 'participants')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Participants</label>
                            <select wire:model="participant_ids" multiple class="w-full border-gray-300 rounded-md min-h-[180px]">
                                @foreach ($participants as $participant)
                                    <option value="{{ $participant->id }}">
                                        {{ $participant->name }} ({{ $participant->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('participant_ids') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($mode === 'group')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-4">
                                <h3 class="font-semibold mb-3">Group A Filters</h3>

                                <label class="block text-sm mb-1">Location</label>
                                <input type="text" wire:model="group_a.location" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Age Min</label>
                                <input type="number" wire:model="group_a.age_min" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Age Max</label>
                                <input type="number" wire:model="group_a.age_max" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Gender</label>
                                <div class="space-y-1">
                                    @foreach (['Male', 'Female', 'Other'] as $gender)
                                        <label class="inline-flex items-center mr-4">
                                            <input type="checkbox" wire:model="group_a.gender" value="{{ $gender }}">
                                            <span class="ml-2">{{ $gender }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="border rounded-lg p-4">
                                <h3 class="font-semibold mb-3">Group B Filters (Optional)</h3>

                                <label class="block text-sm mb-1">Location</label>
                                <input type="text" wire:model="group_b.location" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Age Min</label>
                                <input type="number" wire:model="group_b.age_min" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Age Max</label>
                                <input type="number" wire:model="group_b.age_max" class="w-full border-gray-300 rounded-md mb-3">

                                <label class="block text-sm mb-1">Gender</label>
                                <div class="space-y-1">
                                    @foreach (['Male', 'Female', 'Other'] as $gender)
                                        <label class="inline-flex items-center mr-4">
                                            <input type="checkbox" wire:model="group_b.gender" value="{{ $gender }}">
                                            <span class="ml-2">{{ $gender }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metrics</label>
                        <select wire:model="metrics" multiple class="w-full border-gray-300 rounded-md min-h-[140px]">
                            @foreach ($availableMetrics as $metric)
                                <option value="{{ $metric }}">{{ $metric }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" wire:model="date_from" class="w-full border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" wire:model="date_to" class="w-full border-gray-300 rounded-md">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Granularity</label>
                            <select wire:model="granularity" class="w-full border-gray-300 rounded-md">
                                <option value="day">Daily</option>
                                <option value="week">Weekly</option>
                                <option value="month">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            @if ($report)
                <div class="bg-white shadow sm:rounded-lg p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Summary</h3>

                        @if ($report['type'] === 'group')
                            <div class="text-sm text-gray-600 mt-2">
                                Group A size: {{ $report['group_a_size'] }}
                                @if ($report['group_b_size'] > 0)
                                    | Group B size: {{ $report['group_b_size'] }}
                                @endif
                            </div>
                        @endif

                        <div class="overflow-x-auto mt-4">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-50">
                                <tr>
                                    @foreach (array_keys($report['summary'][0] ?? []) as $heading)
                                        <th class="px-4 py-2 border text-left text-sm font-medium text-gray-700">
                                            {{ \Illuminate\Support\Str::headline($heading) }}
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($report['summary'] as $row)
                                    <tr>
                                        @foreach ($row as $value)
                                            <td class="px-4 py-2 border text-sm text-gray-700">
                                                {{ $value ?? 'N/A' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @foreach ($report['charts'] as $index => $chart)
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-4">
                                {{ \Illuminate\Support\Str::headline($chart['metric']) }}
                            </h4>
                            <canvas id="chart-{{ $index }}" height="100"></canvas>
                        </div>
                    @endforeach
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('livewire:navigated', renderCharts);
                    document.addEventListener('livewire:load', renderCharts);

                    function renderCharts() {
                        const charts = @json($report['charts']);

                        charts.forEach((chart, index) => {
                            const ctx = document.getElementById(`chart-${index}`);
                            if (!ctx) return;

                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: chart.labels,
                                    datasets: chart.datasets.map(dataset => ({
                                        label: dataset.label,
                                        data: dataset.data,
                                        borderWidth: 2,
                                        fill: false,
                                        tension: 0.2,
                                    })),
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            display: true
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        });
                    }
                </script>
            @endif
        </div>
    </div>
</div>
