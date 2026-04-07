@php
    // allow multiple charts later without id collisions
    $chartId = $chartId ?? 'trendChart_' . uniqid();
    $title = $title ?? 'Submission Trend';
    $metric = $metric ?? 'submission_count';
    $groupBy = $groupBy ?? 'week';
@endphp

<div class="bg-white rounded-xl shadow p-4">
    <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">

        <div>
            <h3 class="text-sm font-semibold text-gray-800">{{ $title }}</h3>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <label class="text-xs text-gray-600" for="{{ $chartId }}_group">Group:</label>
            <select id="{{ $chartId }}_group" class="text-sm border-gray-300 rounded-md">
                <option value="day" @selected($groupBy === 'day')>Day</option>
                <option value="week" @selected($groupBy === 'week')>Week</option>
                <option value="month" @selected($groupBy === 'month')>Month</option>
            </select>

            <a
                id="{{ $chartId }}_export"
                href="{{ route('dashboard.trends.export', ['metric' => $metric, 'group_by' => $groupBy]) }}"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-gray-900 text-black hover:bg-gray-800"
            >
                Export CSV
            </a>
        </div>
    </div>

    <div class="relative h-64">
        {{-- Loading overlay --}}
        <div id="{{ $chartId }}_loading"
             class="hidden absolute inset-0 bg-white/70 rounded-lg flex items-center justify-center z-10">
            <div class="text-sm text-gray-700">Loading…</div>
        </div>

        {{-- Empty state --}}
        <div id="{{ $chartId }}_empty"
             class="hidden absolute inset-0 rounded-lg flex items-center justify-center text-sm text-gray-500">
            No data yet (add some form_submissions).
        </div>

        <canvas id="{{ $chartId }}"></canvas>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce

    <script>
        (function () {
            const chartId = @json($chartId);
            const metric = @json($metric);
            const baseTrendsUrl = @json(route('dashboard.trends'));
            const baseExportUrl = @json(route('dashboard.trends.export'));

            const canvas = document.getElementById(chartId);
            const loadingEl = document.getElementById(chartId + "_loading");
            const emptyEl = document.getElementById(chartId + "_empty");
            const groupSelect = document.getElementById(chartId + "_group");
            const exportLink = document.getElementById(chartId + "_export");

            if (!canvas || !window.Chart || !groupSelect || !exportLink) return;

            window.__charts = window.__charts || {};

            function setLoading(on) {
                if (!loadingEl) return;
                loadingEl.classList.toggle('hidden', !on);
            }

            function setEmpty(on) {
                if (!emptyEl) return;
                emptyEl.classList.toggle('hidden', !on);
            }

            function buildUrl(groupBy) {
                const qs = new URLSearchParams({
                    metric,
                    group_by: groupBy,
                }).toString();
                return baseTrendsUrl + "?" + qs;
            }

            function buildExportUrl(groupBy) {
                const qs = new URLSearchParams({
                    metric,
                    group_by: groupBy,
                }).toString();
                return baseExportUrl + "?" + qs;
            }

            async function loadAndRender(groupBy) {
                setEmpty(false);
                setLoading(true);

                try {
                    const res = await fetch(buildUrl(groupBy), {
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        credentials: "same-origin"
                    });

                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data = await res.json();
                    const labels = Array.isArray(data.labels) ? data.labels : [];
                    const values = Array.isArray(data.values) ? data.values : [];

                    // update export link
                    exportLink.href = buildExportUrl(groupBy);

                    // empty state
                    if (labels.length === 0) {
                        // destroy chart if exists
                        if (window.__charts[chartId]) {
                            window.__charts[chartId].destroy();
                            delete window.__charts[chartId];
                        }
                        setEmpty(true);
                        return;
                    }

                    // (re)create chart
                    if (window.__charts[chartId]) window.__charts[chartId].destroy();

                    window.__charts[chartId] = new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Submissions',
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

                } catch (e) {
                    console.error('Trends fetch failed', e);
                    setEmpty(true);
                } finally {
                    setLoading(false);
                }
            }

            // initial render (server default selected option)
            loadAndRender(groupSelect.value);

            // selector change
            groupSelect.addEventListener('change', () => {
                loadAndRender(groupSelect.value);
            });

        })();
    </script>
</div>
