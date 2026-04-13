<div class="h-full w-full flex flex-col bg-white shadow rounded-lg overflow-hidden">
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

    <div class="p-6">
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
</div>
