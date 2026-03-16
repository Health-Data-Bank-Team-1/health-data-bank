<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div>
                <div class="flex justify-center px-4 py-2 mt-4">
                    <x-input id="search" class="mt-1 block w-full" wire:model="search"
                        placeholder="Search patients..." />
                    <x-button wire:click="getPatients()" class="ml-2">Search</x-button>
                </div>
                @if (!empty($found))
                    <ul class="divide-y divide-gray-200 flex-1 overflow-y-auto">
                        @foreach ($found as $acc)
                            <li>
                                <a href="{{ route('provider.patients.show', $acc) }}"
                                    class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 focus:outline-none focus:bg-gray-50 transition">
                                    <span class="text-gray-900 font-medium">{{ $acc->name }}</span>
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <x-section-border />
            </div>
            <div>
                <livewire:provider.patient-index />
            </div>
        </div>
    </div>
</div>
