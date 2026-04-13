<div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-center justify-between mb-2">
        <h3 class="text-sm font-semibold text-gray-800">{{ $chartLabel }} Trend</h3>
        <a
            id="{{ $chartId }}_export"
            href="{{ route('dashboard.trends.export', ['metric' => $curr_metric, 'group_by' => $groupBy]) }}"
            class="text-xs text-blue-600 hover:text-blue-800 underline whitespace-nowrap"
        >Export CSV</a>
    </div>

    <div class="flex items-center gap-2 flex-wrap mb-3">
        @if(count($available_metrics) > 1)
            <label class="text-xs text-gray-600" for="metric_select">Metric:</label>
            <select
                id="metric_select"
                wire:model.live="curr_metric"
                class="text-sm border-gray-300 rounded-md max-w-[10rem]"
            >
                @foreach($available_metrics as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        @endif

        <label class="text-xs text-gray-600" for="group_select">Group By:</label>
        <select
            id="group_select"
            wire:model.live="groupBy"
            class="text-sm border-gray-300 rounded-md max-w-[8rem]"
        >
            <option value="day">Day</option>
            <option value="week">Week</option>
            <option value="month">Month</option>
        </select>
    </div>

    <div class="relative h-64" wire:ignore>
        {{-- Loading overlay --}}
        <div id="{{ $chartId }}_loading"
             class="hidden absolute inset-0 bg-white/70 rounded-lg flex items-center justify-center z-10">
            <div class="text-sm text-gray-700">Loading…</div>
        </div>

        {{-- Empty state --}}
        <div id="{{ $chartId }}_empty"
             class="hidden absolute inset-0 rounded-lg flex items-center justify-center text-sm text-gray-500">
            No data yet (add some form submissions).
        </div>

        <canvas id="{{ $chartId }}"></canvas>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    @script
        <script>
            const canvasId = @js($chartId);

            function renderTrendChart() {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !window.Chart) return;

                window.__charts = window.__charts || {};

                const labels = $wire.chartLabels;
                const values = $wire.chartValues;
                const label = $wire.chartLabel;

                if (window.__charts[canvasId]) {
                    window.__charts[canvasId].destroy();
                    delete window.__charts[canvasId];
                }

                if (!labels || labels.length === 0) return;

                window.__charts[canvasId] = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label,
                            data: values,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            renderTrendChart();

            $wire.on('chart-updated', () => {
                renderTrendChart();
            });
        </script>
    @endscript
</div>
