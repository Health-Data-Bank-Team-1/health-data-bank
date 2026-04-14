<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
            <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Patient Record</h2>
            <a
                href="{{ route('provider.patients') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800"
            >
                ← Back to Patients Page
            </a>
            </div>

            <div class="mt-4 text-sm text-gray-700 space-y-1">
                <p><strong>Name:</strong> {{ $patientAccount->name }}</p>
                <p><strong>Email:</strong> {{ $patientAccount->email }}</p>
                <p><strong>Account ID:</strong> {{ $patientAccount->id }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Entries</h3>

            @if($healthEntries && count($healthEntries))
                <div class="space-y-4">
                    @foreach($healthEntries as $entry)
                        <div class="border rounded-lg p-4">
                            <p class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($entry->timestamp)->format('Y-m-d H:i') }}
                            </p>

                            <div class="mt-2 text-sm text-gray-700">
                                @foreach($entry->encrypted_values as $key => $value)
                                    <p><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-md bg-gray-50 border border-dashed border-gray-300 p-4 text-sm text-gray-600">
                    No health entries available for this patient.
                </div>
            @endif
        </div>

        <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Personalized Feedback</h3>

            @if (session()->has('message'))
                <div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-1">Feedback Notes</label>
                    <textarea
                        id="feedback"
                        wire:model="feedback"
                        rows="4"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="Enter personalized provider feedback..."
                    ></textarea>
                    @error('feedback')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="recommended_actions" class="block text-sm font-medium text-gray-700 mb-1">Recommended Actions</label>
                    <textarea
                        id="recommended_actions"
                        wire:model="recommended_actions"
                        rows="3"
                        class="w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="Enter recommended next steps or actions..."
                    ></textarea>
                    @error('recommended_actions')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <button
                        wire:click="submitFeedback"
                        type="button"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700"
                    >
                        Submit Feedback
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Previous Feedback</h3>

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
                    No provider feedback has been submitted for this patient yet.
                </div>
            @endif
        </div>
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endonce
</div>
