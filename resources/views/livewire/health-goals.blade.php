<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Health Goals</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        View, add, or update your personal health goals.
                    </p>
                </div>

                <a
                    href="{{ route('my-progress') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    Back to My Progress
                </a>
            </div>

            @if (session()->has('success'))
                <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <button
                    wire:click="createGoal"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Add Goal
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Goals</h2>

                    @if(count($goals))
                        <div class="space-y-4">
                            @foreach($goals as $goal)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-700">
                                        <strong>Metric:</strong>
                                        {{ $metricOptions[$goal->metric_key] ?? $goal->metric_key }}
                                    </p>

                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Rule:</strong>
                                        {{ $operatorOptions[$goal->comparison_operator] ?? $goal->comparison_operator }}
                                    </p>

                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Target:</strong> {{ $goal->target_value }}
                                    </p>

                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Timeframe:</strong>
                                        {{ $timeframeOptions[$goal->timeframe] ?? $goal->timeframe }}
                                    </p>

                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Status:</strong> {{ $goal->status }}
                                    </p>

                                    <p class="text-sm text-gray-700 mt-2">
                                        <strong>Active From:</strong> {{ $goal->start_date }}
                                    </p>

                                    @if($goal->end_date)
                                        <p class="text-sm text-gray-700 mt-2">
                                            <strong>End Date:</strong> {{ $goal->end_date }}
                                        </p>
                                    @endif

                                    <div class="mt-4">
                                        <button
                                            wire:click="editGoal('{{ $goal->id }}')"
                                            class="px-3 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                                        >
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No health goals have been created yet.</p>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $editingGoalId ? 'Update Goal' : 'Add Goal' }}
                    </h2>

                    @if($showForm)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Health Metric</label>
                                <select wire:model="metric_key" class="w-full rounded-md border-gray-300">
                                    @foreach($metricOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Goal Type</label>
                                <select wire:model="comparison_operator" class="w-full rounded-md border-gray-300">
                                    @foreach($operatorOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                                <input type="number" wire:model="target_value" class="w-full rounded-md border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Timeframe</label>
                                <select wire:model="timeframe" class="w-full rounded-md border-gray-300">
                                    @foreach($timeframeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" wire:model="start_date" class="w-full rounded-md border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" wire:model="end_date" class="w-full rounded-md border-gray-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select wire:model="status" class="w-full rounded-md border-gray-300">
                                    <option value="ACTIVE">Active</option>
                                    <option value="MET">Met</option>
                                    <option value="EXPIRED">Expired</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    wire:click="save"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Save Goal
                                </button>

                                <button
                                    wire:click="cancelForm"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">
                            Click <strong>Add Goal</strong> to create a new health goal, or choose <strong>Edit</strong> on an existing goal.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
