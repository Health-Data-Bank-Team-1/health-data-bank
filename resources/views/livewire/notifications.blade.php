<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            @forelse ($notifications as $notification)
                <div class="border-b last:border-b-0 py-4 rounded px-3"
                     style="border-left: 4px solid {{ $notification->status === 'unread' ? '#f87171' : '#4ade80' }};">
                    <button
                        wire:click="open({{ $notification->id }})"
                        class="flex items-center justify-between w-full text-left hover:bg-gray-50 rounded p-2 -m-2 transition">
                        <div>
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
                        </div>
                        <svg class="h-5 w-5 text-gray-400 shrink-0 ml-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            @empty
                <p class="text-gray-500">No notifications found.</p>
            @endforelse
            {{ $notifications->links() }}
        </div>
    </div>

    <x-dialog-modal wire:model="showModal">
        <x-slot name="title">
            {{ ucfirst($selectedNotification->type ?? '') }} Notification
        </x-slot>

        <x-slot name="content">
            <p class="text-gray-700">{{ $selectedNotification->message ?? '' }}</p>
            <div class="mt-3 text-sm text-gray-500">
                <span>{{ ($selectedNotification->created_at ?? now())->format('M d, Y h:i A') }}</span>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showModal', false)">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
