<div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">{{ $chartLabel }}</h3>
        </div>

        <div class="flex items-center gap-2 shrink-0 mb-4">
            @if($hasAggregate && $hasTimeseries)
                <button
                    wire:click="setViewMode('aggregate')"
                    @class([
                        'px-3 py-1 text-xs rounded-md border transition-colors',
                        'bg-blue-600 text-white border-blue-600' => $viewMode === 'aggregate',
                        'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' => $viewMode !== 'aggregate',
                    ])
                >Aggregate</button>
                <button
                    wire:click="setViewMode('timeseries')"
                    @class([
                        'px-3 py-1 text-xs rounded-md border transition-colors',
                        'bg-blue-600 text-white border-blue-600' => $viewMode === 'timeseries',
                        'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' => $viewMode !== 'timeseries',
                    ])
                >Timeseries</button>
            @endif
        </div>
    </div>

    @if($viewMode === 'aggregate' && !empty($aggregateMetrics))
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($aggregateMetrics as $metric => $value)
                <div class="border rounded-lg p-2">
                    <p class="text-xs font-medium text-gray-600 truncate mb-1" title="{{ $metric }}">{{ $metric }}</p>
                    <div class="relative h-20">
                        <canvas id="mini-{{ $chartId }}-{{ $loop->index }}"></canvas>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($viewMode === 'timeseries' && !empty($chartLabels))
        <div class="relative h-[500px]">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    @else
        <div class="h-32 flex items-center justify-center text-sm text-gray-500">
            No data available to chart.
        </div>
    @endif

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    @script
        <script>
            const chartId = @js($chartId);

            function renderCharts() {
                if (!window.Chart) return;

                window.__charts = window.__charts || {};

                Object.keys(window.__charts).forEach(k => {
                    window.__charts[k].destroy();
                    delete window.__charts[k];
                });

                if ($wire.viewMode === 'aggregate') {
                    const metrics = $wire.aggregateMetrics;
                    const keys = Object.keys(metrics);
                    const vals = Object.values(metrics);

                    keys.forEach((key, i) => {
                        const canvas = document.getElementById('mini-' + chartId + '-' + i);
                        if (!canvas) return;

                        const id = 'mini-' + chartId + '-' + i;
                        window.__charts[id] = new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: [key],
                                datasets: [{
                                    label: key,
                                    data: [vals[i]],
                                    backgroundColor: 'rgba(59,130,246,0.6)',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ctx.parsed.y !== null ? ctx.parsed.y : 'N/A'
                                        }
                                    }
                                },
                                scales: {
                                    y: { beginAtZero: true },
                                    x: { display: false }
                                }
                            }
                        });
                    });
                } else {
                    const canvas = document.getElementById(chartId);
                    if (!canvas) return;

                    const allLabels = $wire.chartLabels;
                    const datasets = $wire.timeseriesDatasets;

                    if (!allLabels || allLabels.length === 0) return;

                    const colors = [
                        'rgba(59,130,246,0.8)',
                        'rgba(239,68,68,0.8)',
                        'rgba(34,197,94,0.8)',
                        'rgba(234,179,8,0.8)',
                        'rgba(168,85,247,0.8)',
                        'rgba(236,72,153,0.8)',
                    ];

                    const chartDatasets = datasets.map((ds, i) => {
                        const dataMap = {};
                        ds.labels.forEach((l, j) => { dataMap[l] = ds.values[j]; });
                        return {
                            label: ds.label,
                            data: allLabels.map(l => dataMap[l] !== undefined ? dataMap[l] : null),
                            tension: 0.3,
                            borderColor: colors[i % colors.length],
                            backgroundColor: colors[i % colors.length].replace('0.8', '0.1'),
                        };
                    });

                    window.__charts[chartId] = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: allLabels,
                            datasets: chartDatasets,
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            spanGaps: true,
                            scales: {
                                y: {
                                    ticks: {
                                        maxTicksLimit: 15
                                    }
                                }
                            },
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
            }

            setTimeout(() => renderCharts(), 100);

            $wire.on('report-chart-updated', () => {
                setTimeout(() => renderCharts(), 100);
            });
        </script>
    @endscript
</div>
