<div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">{{ $chartLabel }} Trend</h3>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if(count($available_metrics) > 1)
                <label class="text-xs text-gray-600" for="metric_select">Metric:</label>
                <select
                    id="metric_select"
                    wire:model.live="curr_metric"
                    class="text-sm border-gray-300 rounded-md"
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
                class="text-sm border-gray-300 rounded-md"
            >
                <option value="day">Day</option>
                <option value="week">Week</option>
                <option value="month">Month</option>
            </select>
        </div>
    </div>

    <div class="relative h-64" wire:ignore>
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
