<x-admin-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Report Review
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">

                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">Flagged Submission Review</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Review the flagged submission details and decide whether it should be removed.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.reports.flagged') }}"
                        class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline"
                    >
                        ← Back to Flagged Reports
                    </a>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Submission Details</h3>

                        <div class="space-y-3 text-sm text-gray-700">
                            <p><span class="font-semibold text-gray-900">Form:</span> {{ $report->formTemplate->title ?? 'N/A' }}</p>
                            <p><span class="font-semibold text-gray-900">Participant:</span> {{ $report->account->name ?? 'N/A' }}</p>
                            <p><span class="font-semibold text-gray-900">Email:</span> {{ $report->account->email ?? 'N/A' }}</p>
                            <p>
                                <span class="font-semibold text-gray-900">Status:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    {{ $report->status }}
                                </span>
                            </p>
                            <p><span class="font-semibold text-gray-900">Reason:</span> {{ $report->flag_reason ?? 'N/A' }}</p>
                            <p><span class="font-semibold text-gray-900">Flagged At:</span> {{ optional($report->flagged_at)->format('M d, Y h:i A') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Moderation Action</h3>

                        <form method="POST" action="{{ route('admin.reports.delete', $report->id) }}">
                            @csrf
                            @method('DELETE')

                            <div class="mb-4">
                                <label for="deletion_reason" class="block text-sm font-medium text-gray-700 mb-1">
                                    Deletion Reason
                                </label>
                                <textarea
                                    name="deletion_reason"
                                    id="deletion_reason"
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                    required
                                >{{ old('deletion_reason') }}</textarea>

                                @error('deletion_reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="inline-flex items-start">
                                    <input
                                        type="checkbox"
                                        name="confirm_delete"
                                        value="1"
                                        class="mt-1 rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">
                                        I confirm that I want to delete this flagged submission.
                                    </span>
                                </label>

                                @error('confirm_delete')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Delete Submission
                            </button>
                        </form>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Entries</h3>

                    @forelse ($report->healthEntries as $entry)
                        <div class="border rounded-lg p-4 mb-4 bg-white shadow-sm">
                            <div class="mb-3 text-sm text-gray-500">
                                Timestamp: {{ optional($entry->timestamp)->format('M d, Y h:i A') ?? 'N/A' }}
                            </div>

                            <pre class="text-xs bg-gray-100 p-4 rounded-md overflow-x-auto text-gray-800 whitespace-pre-wrap">{{ json_encode($entry->encrypted_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 bg-gray-50">
                            No health entries found for this submission.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>
