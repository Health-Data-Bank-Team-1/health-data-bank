<div class="bg-white rounded-xl shadow p-4">

    <p class="text-sm text-gray-600 mb-4">
        Compare your selected health metric with group aggregate values.
    </p>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Metric</label>
        <select wire:model.live="selectedMetric" class="w-full rounded-md border-gray-300">
            @foreach($metricOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm">
            <thead class="bg-gray-100">
            <tr>
                <th class="px-3 py-2 text-left">Metric</th>
                <th class="px-3 py-2 text-left">Your Value</th>
                <th class="px-3 py-2 text-left">Group Average</th>
                <th class="px-3 py-2 text-left">Difference</th>
            </tr>
            </thead>
            <tbody>
            @foreach($comparisonRows as $row)
                <tr class="border-t">
                    <td class="px-3 py-2">{{ $row['metric'] }}</td>
                    <td class="px-3 py-2">{{ $row['your_value'] !== null ? number_format($row['your_value'], 2) : 'N/A' }}</td>
                    <td class="px-3 py-2">{{ $row['group_average'] !== null ? number_format($row['group_average'], 2) : 'N/A' }}</td>
                    <td class="px-3 py-2">
                        {{ $row['difference'] !== null ? number_format($row['difference'], 2) : 'N/A' }}
                    </td>
                    <p class="mt-3 text-xs text-gray-500">
                        Comparison values will appear once metric extraction from stored health entries is connected.
                    </p>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
