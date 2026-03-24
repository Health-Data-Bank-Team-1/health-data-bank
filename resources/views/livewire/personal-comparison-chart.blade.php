<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Comparison Graph</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Visual comparison between your metric and the anonymized cohort average.
                    </p>
                </div>

                <a
                    href="{{ route('comparison', [
                        'metric_key' => $metric_key,
                        'from' => $from,
                        'to' => $to,
                        'gender' => $gender,
                        'location' => $location,
                        'age_min' => $age_min,
                        'age_max' => $age_max,
                    ]) }}"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    Back to Comparison
                </a>
            </div>

            @if($result && $result['group']['is_suppressed'])
                <div class="rounded-md bg-yellow-100 text-yellow-800 px-4 py-3">
                    Group too small to display aggregate results.
                </div>
            @elseif($result)
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Metric</p>
                        <p class="text-lg font-semibold">
                            {{ $metricOptions[$result['metric_key']] ?? $result['metric_key'] }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Your Value</p>
                        <p class="text-lg font-semibold">{{ $result['user_value'] ?? 'N/A' }}</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Group Average</p>
                        <p class="text-lg font-semibold">{{ $result['group']['avg'] ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <canvas id="comparisonChart" height="120"></canvas>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const ctx = document.getElementById('comparisonChart');

                        if (!ctx) return;

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Your Value', 'Group Average'],
                                datasets: [{
                                    label: '{{ $metricOptions[$result['metric_key']] ?? $result['metric_key'] }}',
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
                    });
                </script>
            @else
                <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4">
                    <p class="text-sm text-gray-600">No comparison data available.</p>
                </div>
            @endif
        </div>
    </div>
</div>
