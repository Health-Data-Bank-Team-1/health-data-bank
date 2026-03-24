<div class="py-12">
    <div class="flex max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex bg-white overflow-hidden shadow-xl sm:rounded-lg">

            <div class="w-1/3 bg-gray-100 p-4">
                <h2 class="text-xl font-semibold mb-4">Reports</h2>
                <livewire:researcher.report-index />
                <div class="flex flex-col items-center mt-2 space-y-1">
                    <x-button class="w-full">{{ 'Search' }}</x-button>
                    <x-button class="w-full">{{ 'Sort' }}</x-button>
                    <x-link-button class="w-full" href="{{ route('researcher.report-generator') }}">
                        New Report
                    </x-link-button>
                </div>
            </div>

            @if ($currReport != null)
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">
                        Report: {{ $currReport->id }}
                    </h2>

                    <div class="flex gap-2">
                        <x-button>Edit</x-button>
                        <x-button>Export as CSV</x-button>
                    </div>
                </div>

                <div class="bg-white rounded p-4 shadow-sm">
                    <livewire:researcher.report-renderer :report="$currReport" />
                </div>
            @else
                <div class="flex items-center justify-center h-full text-gray-500">
                    Select a cohort or create a new report to view aggregated results.
                </div>
            @endif

        </div>
    </div>
</div>
