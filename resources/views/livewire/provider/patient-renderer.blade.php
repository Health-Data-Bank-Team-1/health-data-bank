<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Patients') }}
    </h1>
</x-slot>

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-4 bg-white rounded shadow">
            <div class="mt-4 flex flex-col justify-center items-center">
                <h3 class="text-2xl font-medium text-gray-900 mb-2">
                    {{ $patientAccount->name }}
                </h3>
                @foreach ($healthEntries as $entry)
                    <div class="w-1/2 mx-auto bg-white shadow rounded p-4 mb-2">
                        <span class="block font-bold mb-2">Date:
                            {{ \Illuminate\Support\Str::before($entry['timestamp'], ' ') }}</span>
                        <table class="min-w-full divide-y divide-gray-200 mt-2">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 border">Metric</th>
                                    <th class="px-4 py-2 border">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($entry['encrypted_values'] as $key => $value)
                                    <tr>
                                        <td class="px-4 py-2 border">{{ $key }}</td>
                                        <td class="px-4 py-2 border">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
