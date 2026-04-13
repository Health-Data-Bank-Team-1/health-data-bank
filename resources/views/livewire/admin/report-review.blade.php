<x-admin-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Report Review
        </h1>
    </x-slot>

    @php
        $fieldLabels = $report->formTemplate?->fields
            ? $report->formTemplate->fields->pluck('label', 'metric_key')->toArray()
            : [];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">

                {{-- Back --}}
                <div class="mb-6">
                    <a href="{{ route('admin.reports.flagged') }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        ← Back to Flagged Reports
                    </a>
                </div>

                {{-- Alerts --}}
                @if (session('success'))
                    <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- MAIN GRID --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    {{-- LEFT SIDE --}}
                    <div class="lg:col-span-8 space-y-6">

                        {{-- Report Details --}}
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                            <h2 class="mb-4 text-lg font-semibold text-gray-900">Report Details</h2>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm text-gray-600">Form</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $report->formTemplate?->title ?? 'N/A' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-600">Submitted By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $report->account?->name ?? 'Unknown User' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-600">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $report->status ?? 'FLAGGED' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-600">Flag Reason</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $report->flag_reason ?? 'No reason provided' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-600">Flagged At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ optional($report->flagged_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-600">Submitted At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ optional($report->submitted_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Full Report --}}
                        <div class="rounded-lg border border-gray-200 bg-white p-5">
                            <h2 class="mb-4 text-lg font-semibold text-gray-900">Full Submitted Report</h2>

                            @if($report->healthEntries && $report->healthEntries->count())
                                <div class="space-y-6">
                                    @foreach($report->healthEntries as $entry)
                                        @php
                                            $values = is_array($entry->encrypted_values)
                                                ? $entry->encrypted_values
                                                : json_decode($entry->encrypted_values, true);
                                        @endphp

                                        <div class="border rounded-md p-4">
                                            <div class="flex justify-between mb-3">
                                                <p class="text-sm font-medium text-gray-800">
                                                    Entry {{ $loop->iteration }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ optional($entry->timestamp)->format('Y-m-d H:i') ?? 'N/A' }}
                                                </p>
                                            </div>

                                            @if(is_array($values) && count($values))
                                                <table class="min-w-full border text-sm">
                                                    <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-gray-600">Field</th>
                                                        <th class="px-3 py-2 text-left text-gray-600">Value</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($values as $key => $value)
                                                        <tr class="border-t">
                                                            <td class="px-3 py-2 font-medium text-gray-800">
                                                                {{ $fieldLabels[$key] ?? $key }}
                                                            </td>
                                                            <td class="px-3 py-2 text-gray-700">
                                                                @if(is_array($value))
                                                                    {{ implode(', ', $value) }}
                                                                @elseif(is_bool($value))
                                                                    {{ $value ? 'Yes' : 'No' }}
                                                                @elseif(empty($value))
                                                                    —
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="text-sm text-gray-500">No submitted values.</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-sm text-gray-500">
                                    No submitted data found.
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- RIGHT SIDE (DELETE) --}}
                    <div class="sticky top-6 h-fit border border-red-200 bg-red-50 p-5 rounded-lg">
                        <h2 class="text-lg font-semibold text-red-800 mb-2">Delete Report</h2>

                        <p class="text-sm text-red-700 mb-4">
                            This will remove the report from active dashboards and analytics.
                        </p>

                        <form method="POST"
                              action="{{ route('admin.reports.delete', $report->id) }}"
                              x-data="{ deleting: false }"
                              @submit="deleting = true"
                              class="space-y-4">
                            @csrf
                            @method('DELETE')

                            <textarea
                                name="deletion_reason"
                                rows="3"
                                class="w-full border rounded-md p-2"
                                placeholder="Reason for deletion..."
                                required
                            ></textarea>

                            <div class="rounded-md border bg-yellow-100 p-4">
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="confirm_delete" required>
                                    <span>I confirm that this report will be permanently removed from active search, dashboards, and analytics results.</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700"
                                :disabled="deleting">
                                <span x-show="!deleting">Delete Report</span>
                                <span x-show="deleting">Deleting...</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
