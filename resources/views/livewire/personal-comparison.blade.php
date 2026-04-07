<div class="pt-4 pb-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 mt-4">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="metric_key" class="text-sm font-medium">Metric</label>
                    <select id="metric_key" wire:model="metric_key" class="w-full border rounded p-2">
                        @foreach($metricOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('metric_key')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="gender" class="text-sm font-medium">Gender</label>
                    <input id="gender" type="text" wire:model="gender" class="w-full border rounded p-2">
                    @error('gender')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="location" class="text-sm font-medium">Location</label>
                    <input id="location" type="text" wire:model="location" class="w-full border rounded p-2">
                    @error('location')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="age_min" class="text-sm font-medium">Age Min</label>
                    <input id="age_min" type="number" wire:model="age_min" class="w-full border rounded p-2">
                    @error('age_min')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="age_max" class="text-sm font-medium">Age Max</label>
                    <input id="age_max" type="number" wire:model="age_max" class="w-full border rounded p-2">
                    @error('age_max')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="from" class="text-sm font-medium">From</label>
                    <input id="from" type="date" wire:model="from" class="w-full border rounded p-2">
                    @error('from')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="to" class="text-sm font-medium">To</label>
                    <input id="to" type="date" wire:model="to" class="w-full border rounded p-2">
                    @error('to')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex gap-2 mt-2">
                <button
                    wire:click="compare"
                    class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700"
                >
                    Compare
                </button>

                @if($result)
                    <a
                        href="{{ route('comparison.chart', [
                            'metric_key' => $metric_key,
                            'from' => $from,
                            'to' => $to,
                            'gender' => $gender,
                            'location' => $location,
                            'age_min' => $age_min,
                            'age_max' => $age_max,
                        ]) }}"
                        class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800"
                    >
                        View Graph
                    </a>
                @endif
            </div>

            @if($result)
                @if($result['group']['is_suppressed'])
                    <div class="mt-6 p-4 bg-yellow-100 text-yellow-800 rounded">
                        {{ $result['group']['message'] ?? 'Group too small to display aggregate results.' }}
                    </div>
                @else
                    <div class="mt-6">
                        <table class="min-w-full border">
                            <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 border">Metric</th>
                                <th class="p-3 border">Your Value</th>
                                <th class="p-3 border">Group Average</th>
                                <th class="p-3 border">Sample Size</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="p-3 border">
                                    {{ $metricOptions[$result['metric_key']] ?? $result['metric_key'] }}
                                </td>
                                <td class="p-3 border">{{ $result['user_value'] ?? 'N/A' }}</td>
                                <td class="p-3 border">{{ $result['group']['avg'] ?? 'N/A' }}</td>
                                <td class="p-3 border">{{ $result['group']['count'] ?? 'N/A' }}</td>
                            </tr>
                            </tbody>
                        </table>

                        @if($result && !$result['group']['is_suppressed'])
                            <div class="mt-8 border rounded-lg p-6">
                                <h2 class="text-lg font-semibold mb-4">
                                    Comparison Chart
                                </h2>

                                <canvas id="comparisonChart" height="120"></canvas>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@if($result && !$result['group']['is_suppressed'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', loadChart);
        document.addEventListener('livewire:update', loadChart);

        function loadChart() {
            const canvas = document.getElementById('comparisonChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            if (window.comparisonChart) {
                window.comparisonChart.destroy();
            }

            window.comparisonChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Your Value', 'Group Average'],
                    datasets: [{
                        label: @json($metricOptions[$result['metric_key']] ?? $result['metric_key']),
                        data: [
                            {{ $result['user_value'] ?? 0 }},
                            {{ $result['group']['avg'] ?? 0 }}
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        loadChart();
    </script>
@endif
