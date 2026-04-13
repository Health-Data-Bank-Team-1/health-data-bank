<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Table: {{ $table }}</h1>
            <p class="text-sm text-gray-500">Preview only. Sensitive columns are masked.</p>
        </div>

        <a href="{{ route('admin.database.index') }}"
           class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
            Back
        </a>
    </div>

    <div class="overflow-x-auto border border-gray-200 rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $col)
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 whitespace-nowrap">
                            {{ $col }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rows as $row)
                    <tr>
                        @foreach($columns as $col)
                            <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                {{ is_array($row[$col] ?? null) ? json_encode($row[$col]) : ($row[$col] ?? '') }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-4 text-gray-500" colspan="{{ count($columns) }}">
                            No rows found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $rows->links() }}
    </div>
</div>