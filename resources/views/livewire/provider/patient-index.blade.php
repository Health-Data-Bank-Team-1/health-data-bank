<div class="h-full w-full flex flex-col bg-white shadow rounded-lg overflow-hidden">
    <ul class="divide-y divide-gray-200 flex-1 overflow-y-auto">
        @foreach ($patients as $patient)
            <li>
                <a href="{{ route('provider.patients.show', $patient) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition">
                    <span class="text-gray-900 font-medium">{{ $patient->name }}</span>
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </li>
        @endforeach
    </ul>
</div>

