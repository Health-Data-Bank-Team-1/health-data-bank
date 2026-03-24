<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Reports') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="flex max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex bg-white overflow-hidden shadow-xl sm:rounded-lg">

            <div class="w-1/3 bg-gray-100 p-4">
                <h2 class="text-xl font-semibold mb-4">Reports</h2>
                <livewire:researcher.report-index />
                <div class="flex flex-col items-center mt-2 space-y-1">
                    <x-button class="w-full">{{ 'Search' }}</x-button>
                    <x-button class="w-full">{{ 'Sort' }}</x-button>
                </div>
            </div>

            <div class="w-2/3 bg-gray-100 p-4">
                @if ($currReport != null)
                    <div class="flex justify-center items-center space-x-2">
                        <h2 class="text-xl font-semibold mb-4">ID: {{ $currReport->id }}</h2>
                        <x-button>{{ 'Edit' }}</x-button>
                        <x-button>{{ 'Export as CSV' }}</x-button>
                    </div>
                    <div>
                        {{ 'graphical report here' }}
                    </div>
                    <div>
                        <livewire:researcher.report-renderer :report="$currReport" />
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
