<div>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Patients') }}
        </h1>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-4 bg-white rounded shadow">
                <div class="mt-4 flex flex-col justify-center items-center">
                    <h3 class="text-2xl font-medium text-gray-900 mb-2">
                        {{ $patientAccount->name }}
                    </h3>

                    <div class="w-full md:w-1/2 mx-auto flex items-center gap-3 mb-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600" for="start_date">From:</label>
                            <input
                                type="date"
                                id="start_date"
                                wire:model.live="startDate"
                                class="text-sm border-gray-300 rounded-md"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600" for="end_date">To:</label>
                            <input
                                type="date"
                                id="end_date"
                                wire:model.live="endDate"
                                class="text-sm border-gray-300 rounded-md"
                            />
                        </div>
                    </div>

                    @if(!empty($metrics))
                        <div class="w-full md:w-3/4 mx-auto mb-4">
                            <div class="bg-white rounded-xl shadow p-4">
                                <h3 class="text-sm font-semibold text-gray-800 mb-3">Health Metrics Over Time</h3>

                                <div
                                    class="relative h-72"
                                    wire:key="chart-{{ $startDate }}-{{ $endDate }}"
                                    x-data='{
                                        labels: @json($chartLabels),
                                        datasets: @json($chartDatasets)
                                    }'
                                    x-init="
                                        const canvas = $el.querySelector('canvas');
                                        if (!canvas) return;

                                        const draw = () => {
                                            if (!window.Chart) return false;
                                            if (!datasets || datasets.length === 0) return true;

                                            new Chart(canvas, {
                                                type: 'line',
                                                data: {
                                                    labels: labels,
                                                    datasets: datasets.map(ds => ({
                                                        ...ds,
                                                        tension: 0.3,
                                                        fill: false,
                                                        pointRadius: 3,
                                                        borderWidth: 2,
                                                    }))
                                                },
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: false,
                                                    spanGaps: true,
                                                    scales: { y: { beginAtZero: false } },
                                                    plugins: {
                                                        legend: { position: 'bottom' }
                                                    }
                                                }
                                            });
                                            return true;
                                        };

                                        if (!draw()) {
                                            let tries = 0;
                                            const iv = setInterval(() => {
                                                tries++;
                                                if (draw() || tries > 25) clearInterval(iv);
                                            }, 200);
                                        }
                                    "
                                >
                                    <canvas id="patientChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-1/2 mx-auto overflow-x-auto mb-4">
                            <table class="min-w-full border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-4 py-2 border text-left">Metric</th>
                                        <th class="px-4 py-2 border text-left">Avg</th>
                                        <th class="px-4 py-2 border text-left">Min</th>
                                        <th class="px-4 py-2 border text-left">Max</th>
                                        <th class="px-4 py-2 border text-left">Latest</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($metrics as $key => $stats)
                                        <tr>
                                            <td class="px-4 py-2 border font-medium text-gray-800">
                                                {{ $availableMetrics[$key] ?? $key }}
                                            </td>
                                            <td class="px-4 py-2 border text-gray-700">{{ $stats['avg'] }}</td>
                                            <td class="px-4 py-2 border text-gray-700">{{ $stats['min'] }}</td>
                                            <td class="px-4 py-2 border text-gray-700">{{ $stats['max'] }}</td>
                                            <td class="px-4 py-2 border text-gray-700">{{ $stats['latest'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="w-full md:w-1/2 mx-auto rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600 mb-4">
                            No data available for the selected date range.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce
</div>
