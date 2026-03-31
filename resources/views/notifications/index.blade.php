<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Notifications
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @forelse ($notifications as $notification)
                    <div class="border-b py-4">
                        <p class="font-medium">{{ $notification->message }}</p>
                        <p class="text-sm text-gray-500">
                            Type: {{ $notification->type }} |
                            Status: {{ $notification->status }} |
                            {{ $notification->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>
                @empty
                    <p class="text-gray-500">No notifications found.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
