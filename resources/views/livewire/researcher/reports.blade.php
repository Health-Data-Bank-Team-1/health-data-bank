<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Reports') }}
    </h1>
</x-slot>

<div class="py-12" x-data="{ showReports: false }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row bg-white overflow-hidden shadow-xl sm:rounded-lg">

            <div class="md:hidden bg-gray-200 p-4">
                <button @click="showReports = !showReports" class="w-full flex justify-between items-center text-gray-700 font-bold focus:outline-none">
                    <span>Reports Menu</span>
                    <svg :class="showReports ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div :class="showReports ? 'block' : 'hidden md:block'" class="w-full md:w-1/3 bg-gray-100 p-4 border-b md:border-b-0 md:border-r border-gray-200">
                <h2 class="text-xl font-semibold mb-4 hidden md:block">Reports</h2>
                <livewire:researcher.report-index />
                <div class="flex flex-col items-center mt-2 space-y-1">
                    <x-button class="w-full">{{ 'Search' }}</x-button>
                    <x-button class="w-full">{{ 'Sort' }}</x-button>
                </div>
            </div>

            <div class="w-full md:w-2/3 bg-gray-100 p-4">
                @if ($currReport != null)
                    <div class="flex flex-col sm:flex-row justify-center sm:justify-between items-center space-y-3 sm:space-y-0 mb-4">
                        <h2 class="text-xl font-semibold">ID: {{ $currReport->id }}</h2>
                        <div class="flex space-x-2">
                            <x-button>{{ 'Edit' }}</x-button>
                            <x-button>{{ 'Export as CSV' }}</x-button>
                        </div>
                    </div>
                    <div class="mb-4">
                        {{ 'graphical report here' }}
                    </div>
                    <div>
                        <livewire:researcher.report-renderer :report="$currReport" />
                    </div>
                @else
                    <div class="flex h-full items-center justify-center text-gray-500">
                        Please select a report from the menu.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>