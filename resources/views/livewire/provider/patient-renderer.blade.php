<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="mt-4">
                {{ $patientAccount->name }}
                @foreach ($healthEntries as $entry)
                    <div class="w-1/2 mx-auto bg-white shadow rounded p-4 mb-2">
                        <span>{{ $entry['timestamp'] }}</span>
                    </div>
                    @foreach ($entry['encrypted_values'] as $key => $value)
                        <div>
                            {{ $key }} | {{ $value }}
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>
