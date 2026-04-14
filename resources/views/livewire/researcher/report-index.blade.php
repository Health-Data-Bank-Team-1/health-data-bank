<div class="h-full w-full flex flex-col bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-900">Saved Reports</h2>
        <p class="text-sm text-gray-600 mt-1">Select a report to view its aggregated results.</p>
    </div>

    <ul class="divide-y divide-gray-200 flex-1 overflow-y-auto">
        @forelse ($reports as $report)
            <li>
                <button
                    wire:click="$dispatchTo('researcher.researcher-reports', 'reportSelected', '{{ $report->id }}')"
                    class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition text-left"
                >
                    <div>
                        <p class="text-gray-900 font-medium">
                            {{ $report->title ?? $report->name ?? 'Report '.$loop->iteration }}
                        </p>

                        <p class="text-sm text-gray-500 mt-1">
                            ID: {{ $report->id }}
                        </p>

                        @if(!empty($report->created_at))
                            <p class="text-xs text-gray-400 mt-1">
                                Created {{ \Carbon\Carbon::parse($report->created_at)->format('Y-m-d H:i') }}
                            </p>
                        @endif
                    </div>

                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </li>
        @empty
            <li class="px-6 py-4 text-sm text-gray-500">
                No reports available yet.
            </li>
        @endforelse
    </ul>
</div>
