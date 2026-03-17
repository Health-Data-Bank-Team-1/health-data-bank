<div class="h-full w-full flex flex-col bg-white shadow rounded-lg overflow-hidden">
    @if ($metrics != null)
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border">Metric</th>
                        <th class="px-4 py-2 border">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($metrics as $key => $value)
                        <tr class="text-center">
                            <td class="px-4 py-2 border">{{ $key }}</td>
                            <td class="px-4 py-2 border">{{ $value }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
