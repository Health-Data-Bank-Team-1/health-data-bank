<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Dashboard') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('researcher.forms') }}">Forms</a>
                        </h2>
                    </div>
                    <x-link-button class="w-full mt-2" href="{{ route('researcher.forms') }}">
                        Manage Forms
                    </x-link-button>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('researcher.reports') }}">Reports</a>
                        </h2>
                    </div>
                    <x-link-button class="w-full mt-2" href="{{ route('researcher.reports') }}">
                        Manage Reports
                    </x-link-button>
                    <x-link-button class="w-full mt-2" href="{{ route('researcher.report-generator') }}">
                        Cohort Report Generator
                    </x-link-button>
                </div>
            </div>
        </div>
    </div>
</div>
