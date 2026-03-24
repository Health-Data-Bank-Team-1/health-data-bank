<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Form Review') }}
    </h1>
</x-slot>

<div class="p-6 space-y-4">

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="p-3 rounded bg-green-100 text-black">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-3 rounded bg-red-100 text-black">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex gap-3 items-center">
        <x-label for="search" value="Search"/>
        <input
            id="search"
            type="text"
            wire:model.debounce.400ms="search"
            placeholder="Search title..."
            class="w-full max-w-md rounded border-gray-300 focus:ring focus:ring-indigo-200"
        />

        <x-label for="approvalStatus" value="Status"/>
        <select
            id="approvalStatus"
            wire:model="approvalStatus"
            class="rounded border-gray-300 focus:ring focus:ring-indigo-200"
        >
            <option value="">All statuses</option>
            <option value="draft">draft</option>
            <option value="pending">pending</option>
            <option value="approved">approved</option>
            <option value="rejected">rejected</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white border rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
            <tr class="text-left">
                <th class="px-6 py-3 font-semibold text-gray-700">Title</th>
                <th class="px-6 py-3 font-semibold text-gray-700 whitespace-nowrap">Version</th>
                <th class="px-6 py-3 font-semibold text-gray-700 whitespace-nowrap">Approval Status</th>
                <th class="px-6 py-3 font-semibold text-gray-700 whitespace-nowrap">Created</th>
                <th class="px-6 py-3 font-semibold text-gray-700 whitespace-nowrap">Actions</th>
            </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
            @forelse ($templates as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $t->title }}</td>

                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $t->version }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($t->approval_status === 'approved') bg-green-100 text-green-800
                                @elseif($t->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($t->approval_status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ $t->approval_status }}
                            </span>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                        {{ $t->created_at ? $t->created_at->toDayDateTimeString() : '—' }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        @if ($t->approval_status === 'pending')
                            <button
                                class="inline-flex items-center px-3 py-1 rounded
                                           !bg-green-600 !text-white
                                           hover:!bg-green-700
                                           focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                                wire:click="approve('{{ $t->id }}')"
                            >
                                Approve
                            </button>

                            <button
                                class="inline-flex items-center px-3 py-1 rounded
                                           bg-red-600 text-white
                                           hover:bg-red-700
                                           focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                wire:click="openReject('{{ $t->id }}')"
                            >
                                Reject
                            </button>
                        @else
                            <span class="text-gray-400 text-sm italic">
                                    No actions
                                </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-6 text-gray-500 text-center" colspan="5">
                        No form templates found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $templates->links() }}
    </div>

    {{-- Reject modal --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg space-y-4">
                <h2 class="text-lg font-semibold">Reject Template</h2>

                <div>
                    <label class="block text-sm font-medium">Reason</label>
                    <input
                        type="text"
                        wire:model.defer="rejectReason"
                        class="w-full rounded border-gray-300 focus:ring focus:ring-red-200"
                        placeholder="e.g. Missing required fields"
                    />
                    @error('rejectReason')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        class="px-3 py-1 rounded border hover:bg-gray-100"
                        wire:click="$set('showRejectModal', false)"
                    >
                        Cancel
                    </button>

                    <button
                        class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                        wire:click="reject"
                    >
                        Reject
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>


