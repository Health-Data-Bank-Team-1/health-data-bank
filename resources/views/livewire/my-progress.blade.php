<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('My Progress') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <div class="bg-white shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Health Summary
                        </h2>
                        <a
                            href="{{ route('health-summary') }}"
                            class="text-sm font-medium text-gray-600 hover:text-gray-800"
                        >
                            View Summary
                        </a>
                    </div>

                    <div>
                        <livewire:health-summary />
                    </div>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Compare to Group
                        </h2>
                    </div>

                    <div>
                        <livewire:personal-comparison />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('health-goals') }}">Health Goals</a>
                        </h2>

                        <a href="{{ route('health-goals') }}"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Manage Goals
                        </a>
                    </div>

                    <div class="mt-4">
                        @if (count($goals))
                            <div class="space-y-4">
                                @foreach ($goals as $goal)
                                    <div class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm">
                                        <p class="text-sm text-gray-500 mb-1">Goal</p>

                                        <p class="text-base font-medium text-gray-900">
                                            {{ $this->goalSummary($goal) }}
                                        </p>

                                        <p class="mt-2 text-sm text-gray-600">
                                            Status: <strong>{{ $goal->status }}</strong>
                                        </p>

                                        <p class="text-sm text-gray-600">
                                            Active from <strong>{{ $goal->start_date }}</strong>
                                            @if ($goal->end_date)
                                                to <strong>{{ $goal->end_date }}</strong>
                                            @endif
                                        </p>

                                        @if (isset($goalProgress[$goal->id]))
                                            <div class="mt-3 text-sm text-gray-600">
                                                <p>
                                                    Your progress:
                                                    <strong>{{ $goalProgress[$goal->id]['current'] }}</strong>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm">
                                No health goals have been set yet. Click Manage Goals to add one.
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Suggestions
                        </h2>
                    </div>

                    <div>
                        <livewire:user-suggestions />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
