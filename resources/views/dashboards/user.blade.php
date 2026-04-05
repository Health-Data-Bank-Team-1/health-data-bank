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
                            <a href="{{ route('user-form-select') }}">Forms</a>
                        </h2>
                    </div>
                    <div>
                        <livewire:form-index />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('my-progress') }}">My Progress</a>
                            </h2>
                            <a
                                href="{{ route('my-progress') }}"
                                class="text-sm font-medium text-gray-600 hover:text-gray-800"
                            >
                                View Progress
                            </a>
                        </div>
                        @include('livewire.trend-chart', [
                        'title' => 'Submission Trend',
                        'metric' => 'submission_count',
                        'groupBy' => 'day' ])
                    </div>
                    <div>
                        <div class="flex items-center">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <a href="{{ route('user-todo') }}">TODO</a>
                            </h2>
                        </div>
                        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
                            todo here
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
                            <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:underline">
                                View all
                            </a>
                        </div>
                        <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                            @php
                                $notifications = \App\Models\Notification::where('account_id', auth()->user()->account_id)
                                    ->where('status', 'unread')
                                    ->latest()
                                    ->take(3)
                                    ->get();
                            @endphp

                            @forelse ($notifications as $notification)
                                <div class="border-b last:border-b-0 py-4 rounded px-3"
                                     style="{{ $notification->status === 'unread' ? 'background-color: #fee2e2; border-left: 4px solid #f87171;' : 'background-color: #dcfce7; border-left: 4px solid #4ade80;' }}">
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="block hover:bg-gray-50 rounded p-2 -m-2 transition">
                                        <p class="font-medium text-gray-900">{{ $notification->message }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ ucfirst($notification->status) }} • {{ $notification->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </a>
                                </div>
                            @empty
                                <p class="text-gray-500">No unread notifications.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
