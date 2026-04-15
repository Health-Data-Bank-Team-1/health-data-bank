<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <div>
        <p class="mt-1 text-sm text-gray-600">
            Review system activity, monitor access to data, and export filtered audit records.
        </p>
    </div>

    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Filters</h2>
                <p class="text-sm text-gray-500">
                    Narrow the audit log by time range, actor, action, and resource.
                </p>
            </div>

            <button
                wire:click="resetFilters"
                type="button"
                class="text-sm text-gray-600 hover:text-gray-900"
            >
                Reset Filters
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Range</label>
                <select
                    wire:model.live="presetRange"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full"
                >
                    <option value="24h">Last 24 hours</option>
                    <option value="72h">Last 72 hours</option>
                    <option value="7d">Last 7 days</option>
                    <option value="custom">Custom range</option>
                </select>
                @if ($presetRange === 'custom')
                    <p class="text-xs text-gray-500 mt-1">
                        Choose a start and end date below.
                    </p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                <select
                    wire:model.live="sortDirection"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full"
                >
                    <option value="desc">Newest first</option>
                    <option value="asc">Oldest first</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input
                    type="date"
                    wire:model.live="from"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input
                    type="date"
                    wire:model.live="to"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Actor</label>
                <input
                    type="text"
                    placeholder="Actor name, email, or User ID"
                    wire:model.live="userId"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Actions</label>

                <select wire:model="event" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Events</option>

                    @foreach($events as $eventOption)
                        <option value="{{ $eventOption }}">
                            {{ $eventOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm text-gray-500">Matching Events</p>
            <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $totalEvents }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-medium text-gray-900">Audit Events</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Matching audit records in chronological order.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                {{ $audits->total() }} result(s)
            </div>
            <div class="flex justify-end">
                <button
                    wire:click="exportCsv"
                    type="button"
                    class="inline-flex items-center bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700"
                >
                    Export CSV
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Timestamp</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Actor</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Action</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Target Type</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Target ID</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">URL</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">IP Address</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-600">Tags</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($audits as $audit)
                    @php
                        $isRisky = in_array($audit->event, ['login_failure', 'access_denied']);

                        $type = $audit->auditable_type;
                        if ($type) {
                            $type = class_basename($type);
                        }

                        $tags = $audit->tags;
                        if (is_string($tags)) {
                            $cleaned = str_replace(['[', ']', '"'], '', $tags);
                            $tags = array_filter(array_map('trim', explode(',', $cleaned)));
                        }
                        $actorName = $audit->name ?? '';
                    @endphp

                    <tr class="hover:bg-gray-50 {{ $isRisky ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3">
                            @php
                                $time = \Carbon\Carbon::parse($audit->created_at)
                                    ->timezone(config('app.timezone'));
                            @endphp
                            <div class="whitespace-nowrap">
                                <div class="text-gray-900">
                                    {{ $time->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $time->format('h:i A') }}
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            @if ($actorName)
                                <div class="font-medium text-gray-900">{{ $actorName }}</div>
                                <div class="text-xs font-mono text-gray-500 break-all">{{ $audit->user_id }}</div>
                            @elseif ($audit->email)
                                <div class="font-medium text-gray-900">{{ $audit->email }}</div>
                                <div class="text-xs font-mono text-gray-500 break-all">{{ $audit->user_id }}</div>
                            @else
                                <span class="text-xs font-mono break-all">
                                    {{ $audit->user_id ?: 'System' }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if ($audit->event === 'login_success')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-emerald-200 text-emerald-900">
                                    Login Success
                                </span>
                            @elseif ($audit->event === 'login_failure')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700">
                                    Login Failure
                                </span>
                            @elseif ($audit->event === 'logout')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-amber-200 text-gray-700">
                                    Logout
                                </span>
                            @elseif ($audit->event === 'access_denied')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-700">
                                    Access Denied
                                </span>
                            @elseif ($audit->event === 'profile_updated')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-100 text-indigo-700">
                                    Profile Updated
                                </span>
                            @elseif ($audit->event === 'form_submitted')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700">
                                    Form Submitted
                                </span>
                            @elseif ($audit->event === 'data_exported')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-500">
                                    Data Exported
                                </span>
                            @elseif ($audit->event === 'provider_data_accessed')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-500 text-indigo-700">
                                    Provider Data Access
                                </span>
                            @elseif ($audit->event === 'health_record_viewed')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-600 text-blue-900">
                                    Health Record Viewed
                                </span>
                            @elseif ($audit->event === 'reporting_trends_view')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700">
                                    Viewed Trends Report
                                </span>
                            @elseif ($audit->event === 'reporting_summary_view')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700">
                                    Viewed Summary Report
                                </span>
                            @elseif ($audit->event === 'researcher_cohort_generated')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-700">
                                    Generated Cohort
                                </span>
                            @elseif ($audit->event === 'researcher_aggregated_report_viewed')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-indigo-100 text-indigo-700">
                                    Viewed Aggregated Report
                                </span>
                            @elseif ($audit->event === 'researcher_aggregated_report_exported')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-500">
                                    Exported Report
                                </span>
                            @elseif ($audit->event === 'audit_log_exported')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-500">
                                    Audit Log Exported
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-700">
                                    {{ $audit->event }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $type ?: '—' }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            <span class="break-all">
                                {{ $audit->auditable_id ?: '—' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-gray-700 max-w-xs">
                            <div class="truncate" title="{{ $audit->url }}">
                                {{ $audit->url ?: '—' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ $audit->ip_address ?: '—' }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            @if (!empty($tags))
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($tags as $tagItem)
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">
                                            {{ trim($tagItem) }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            No audit events match the selected filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $audits->firstItem() ?? 0 }} to {{ $audits->lastItem() ?? 0 }} of {{ $audits->total() }} results
            </div>

            <div>
                {{ $audits->links() }}
            </div>
        </div>
    </div>

</div>
