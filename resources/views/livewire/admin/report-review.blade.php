<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Report Review
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg p-6">
                <div class="mb-6">
                    <a href="{{ route('admin.reports.flagged') }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        ← Back to Flagged Reports
                    </a>
                </div>

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

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Report Details</h2>

                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Report Type</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $report->report_type ?? 'N/A' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Researcher</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $report->researcher?->name ?? 'Unknown' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Moderation Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $report->moderation_status ?? 'FLAGGED' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Moderation Reason</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $report->moderation_reason ?? 'No reason provided' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Moderated At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ optional($report->moderated_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-600">Archived</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $report->is_archived ? 'Yes' : 'No' }}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-red-200 bg-red-50 p-5">
                        <h2 class="mb-2 text-lg font-semibold text-red-800">Delete Report</h2>
                        <p class="mb-4 text-sm text-red-700">
                            Deleting this report removes it from active use. Metadata should still remain available for audit purposes.
                        </p>

                        <form method="POST" action="{{ route('admin.reports.delete', $report->id) }}" class="space-y-4">
                            @csrf
                            @method('DELETE')

                            <div>
                                <label for="deletion_reason" class="block text-sm font-medium text-gray-700">
                                    Reason for deletion
                                </label>
                                <textarea
                                    id="deletion_reason"
                                    name="deletion_reason"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                    placeholder="Explain why this report is being deleted..."
                                    required
                                >{{ old('deletion_reason', $report->deletion_reason) }}</textarea>

                                @error('deletion_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="rounded-md border border-yellow-200 bg-yellow-50 p-4">
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        name="confirm_delete"
                                        value="1"
                                        class="mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                        required
                                    >
                                    <span class="text-sm text-yellow-800">
                                        I confirm that this report should be permanently removed from active search, dashboards, and analytics results.
                                    </span>
                                </label>

                                @error('confirm_delete')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <a href="{{ route('admin.reports.flagged') }}"
                                   class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>

                                <button
                                    type="submit"
                                    onclick="return confirm('Are you sure you want to delete this flagged report?')"
                                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                >
                                    Delete Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($report->updates && $report->updates->count())
                    <div class="mt-8 rounded-lg border border-gray-200 bg-white p-5">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Report Update History</h2>

                        <div class="space-y-4">
                            @foreach($report->updates as $update)
                                <div class="rounded-md border border-gray-200 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-800">
                                            Update #{{ $loop->iteration }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ optional($update->created_at)->format('Y-m-d H:i') ?? 'N/A' }}
                                        </p>
                                    </div>

                                    @if(!empty($update->notes))
                                        <p class="mt-2 text-sm text-gray-700">
                                            {{ $update->notes }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
