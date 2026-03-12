<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <a href="{{ route('health-summary') }}">Health Summary</a>
                        </h2>
                    </div>
                    <div>
                        @include('livewire.trend-chart', [
                            'title' => 'Submission Trend',
                            'metric' => 'submission_count',
                            'groupBy' => 'day' ])
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('health-goals') }}">Health Goals</a>
                            </h2>

                            <a
                                href="{{ route('health-goals') }}"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                            >
                                Manage Goals
                            </a>
                        </div>

                        <div class="mt-4">
                            @if(count($goals))
                                <div class="space-y-4">
                                    @foreach($goals as $goal)
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
                                                @if($goal->end_date)
                                                    to <strong>{{ $goal->end_date }}</strong>
                                                @endif
                                            </p>

                                            @if(isset($goalProgress[$goal->id]))
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
                                <a
                                    href="{{ route('health-goals') }}"
                                    class="block rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-600 hover:bg-gray-50"
                                >
                                    No health goals have been set yet. Click here to add one.
                                </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('my-progress') }}">Compare to Group</a>
                            </h2>
                        </div>

                        <div class="mt-4">
                            <livewire:compare-group />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
