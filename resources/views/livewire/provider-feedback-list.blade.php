<div class="bg-white shadow rounded-lg p-6 border border-gray-100">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Provider Feedback</h3>

    @if(count($feedbackEntries))
        <div class="space-y-4">
            @foreach($feedbackEntries as $entry)
                <div class="border rounded-lg p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $entry['provider_name'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $entry['created_at'] }}</p>
                        </div>
                    </div>

                    <div class="mt-3 text-sm text-gray-700">
                        <p><strong>Feedback:</strong> {{ $entry['feedback'] }}</p>

                        @if(!empty($entry['recommended_actions']))
                            <p class="mt-2"><strong>Recommended Actions:</strong> {{ $entry['recommended_actions'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600">
            No provider feedback is available yet.
        </div>
    @endif
</div>
