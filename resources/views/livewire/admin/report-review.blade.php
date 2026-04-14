<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session()->has('message'))
            <div class="bg-green-100 text-green-800 p-3 rounded text-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Reports</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Review stored reports and select one to inspect.
                    </p>

                    <div class="mt-4">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by title, name, description, or ID..."
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >
                    </div>
                </div>

                <div class="divide-y divide-gray-200 max-h-[36rem] overflow-y-auto">
                    @forelse ($reports as $report)
                        <button
                            wire:click="selectReport('{{ $report->id }}')"
                            type="button"
                            class="w-full text-left px-4 py-4 hover:bg-gray-50 transition {{ $selectedReport && $selectedReport->id === $report->id ? 'bg-indigo-50' : '' }}"
                        >
                            <div class="font-medium text-gray-900">
                                {{ $report->title ?? $report->name ?? 'Report '.$loop->iteration }}
                            </div>

                            <div class="text-sm text-gray-500 mt-1 break-all">
                                {{ $report->id }}
                            </div>

                            @if(!empty($report->created_at))
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ \Carbon\Carbon::parse($report->created_at)->format('Y-m-d H:i') }}
                                </div>
                            @endif
                        </button>
                    @empty
                        <div class="p-4 text-sm text-gray-500">
                            No reports available.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-2 bg-white shadow rounded-lg border border-gray-100 overflow-hidden">
                <div class="p-6">
                    @if($selectedReport)
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    {{ $selectedReport->title ?? $selectedReport->name ?? 'Selected Report' }}
                                </h2>

                                <div class="mt-3 text-sm text-gray-600 space-y-1">
                                    <p><strong>ID:</strong> {{ $selectedReport->id }}</p>

                                    @if(!empty($selectedReport->created_at))
                                        <p><strong>Created:</strong> {{ \Carbon\Carbon::parse($selectedReport->created_at)->format('Y-m-d H:i') }}</p>
                                    @endif

                                    @if(!empty($selectedReport->updated_at))
                                        <p><strong>Updated:</strong> {{ \Carbon\Carbon::parse($selectedReport->updated_at)->format('Y-m-d H:i') }}</p>
                                    @endif

                                    @if(isset($selectedReport->researcher_id))
                                        <p><strong>Researcher ID:</strong> {{ $selectedReport->researcher_id }}</p>
                                    @endif
                                </div>
                            </div>

                            <button
                                wire:click="deleteSelectedReport"
                                onclick="return confirm('Are you sure you want to delete this report? This action cannot be undone from the UI.')"
                                type="button"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                            >
                                Delete Report
                            </button>
                        </div>

                        <div class="mt-6 space-y-6">
                            @if(!empty($selectedReport->description))
                                <div>
                                    <h3 class="text-md font-semibold text-gray-900 mb-2">Description</h3>
                                    <div class="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                                        {{ $selectedReport->description }}
                                    </div>
                                </div>
                            @endif

                            @if(isset($selectedReport->aggregatedData) && !empty($selectedReport->aggregatedData))
                                <div>
                                    <h3 class="text-md font-semibold text-gray-900 mb-2">Aggregated Report Data</h3>
                                    <div class="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 overflow-x-auto">
                                        <pre class="whitespace-pre-wrap break-words">{{ json_encode($selectedReport->aggregatedData, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif

                            @if(empty($selectedReport->description) && empty($selectedReport->aggregatedData))
                                <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                                    This report has limited display metadata, but it can still be reviewed and removed by an administrator.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-6 text-sm text-gray-600">
                            Select a report to review its details.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
