<div class="w-full flex flex-col bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">
            {{ $report->title ?? $report->name ?? 'Research Report' }}
        </h2>

        <div class="mt-2 text-sm text-gray-600 space-y-1">
            <p><strong>ID:</strong> {{ $report->id }}</p>

            @if(!empty($report->created_at))
                <p><strong>Created:</strong> {{ \Carbon\Carbon::parse($report->created_at)->format('Y-m-d H:i') }}</p>
            @endif

            @if(!empty($report->description))
                <p><strong>Description:</strong> {{ $report->description }}</p>
            @endif
        </div>
    </div>

    @php
        $tsMap = [];
        foreach ($timeseriesRows as $row) {
            $metric = is_array($row) ? ($row['metric'] ?? null) : ($row->metric ?? null);
            $points = is_array($row) ? ($row['points'] ?? []) : ($row->points ?? []);
            if ($metric) {
                $tsMap[$metric] = [
                    'metric' => $metric,
                    'points' => is_string($points) ? json_decode($points, true) ?? [] : $points,
                ];
            }
        }
    @endphp

    <div class="p-6">
        <livewire:researcher.report-chart
            :aggregate-data="$metrics"
            :timeseries-data="$tsMap"
            :key="$report->id"
        />
    </div>

    <div class="p-6 pt-0">
        @if (!empty($metrics))
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border text-left">Metric</th>
                        <th class="px-4 py-2 border text-left">Value</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($metrics as $key => $value)
                        <tr>
                            <td class="px-4 py-2 border font-medium text-gray-800">{{ $key }}</td>
                            <td class="px-4 py-2 border text-gray-700">
                                {{ is_array($value) ? json_encode($value) : $value }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                No aggregated metrics are available for this report yet.
            </div>
        @endif
    </div>

    @if (!empty($notes))
        <div class="px-6 pb-6 mb-6 pt-0">
            <h3 class="text-md font-semibold text-gray-900 mb-2">Notes</h3>
            <ul class="space-y-2">
                @foreach ($notes as $note)
                    <li class="bg-gray-50 rounded-lg p-3 text-sm">
                        <p class="text-gray-700">{{ $note['content'] }}</p>
                        @if(!empty($note['created_at']))
                            <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($note['created_at'])->format('Y-m-d H:i') }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
