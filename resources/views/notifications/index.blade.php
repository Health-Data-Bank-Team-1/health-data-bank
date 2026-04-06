<x-user-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notifications') }}

        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @forelse ($notifications as $notification)
                    <div class="border-b last:border-b-0 py-4 rounded px-3"
                         style="{{ $notification->status === 'unread' ? 'background-color: #fee2e2; border-left: 4px solid #f87171;'
                         : 'background-color: #dcfce7; border-left: 4px solid #4ade80;' }}">
                        @if ($notification->link)
                            <a href="{{ route('notifications.open', $notification->id) }}" class="block hover:bg-gray-50 rounded p-2 -m-2 transition">
                                <p class="font-medium">
                                    {{ $notification->message }}
                                </p>

                                <div class="mt-1 text-sm text-gray-700">
                                    <span class="mr-4">Type: {{ ucfirst($notification->type) }}</span>
                                    <span class="mr-4 px-2 py-1 rounded text-xs font-semibold
                                     {{ $notification->status === 'unread' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ ucfirst($notification->status) }}
                                    </span>
                                    <span>{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </a>
                        @else
                            <p class="font-medium text-gray-700">
                                {{ $notification->message }}
                            </p>

                            <div class="mt-1 text-sm text-gray-500">
                                <span class="mr-4">Type: {{ ucfirst($notification->type) }}</span>
                                <span class="mr-4">Status: {{ ucfirst($notification->status) }}</span>
                                <span>{{ $notification->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500">No notifications found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-user-layout>
