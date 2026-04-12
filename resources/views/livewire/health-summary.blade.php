<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="flex flex-col mb-4 mt-4">

                <div class="flex flex-col items-center gap-2">
                    <div>
                        <x-label for="from" value="Start Date" />
                        <x-input wire:model="from" class="mt-1 block" type="date" />
                        @error('from')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <x-label for="to" value="End Date" />
                        <x-input wire:model="to" class="mt-1 block" type="date" />
                        @error('to')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col p-4">
                    <div wire:loading wire:target="loadSummary">
                        {{ 'Calculating summary...' }}
                    </div>
                    <x-button wire:click="loadSummary" wire:loading.attr="disabled" wire:target="loadSummary">
                        {{ 'Load Summary' }}
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <x-dialog-modal wire:model="showAverages">
        <x-slot name="title">
            Health Summary Averages
        </x-slot>

        <x-slot name="content">
            @foreach ($avgs as $key => $value)
                <div class="bg-white shadow rounded p-4 mb-2">
                    <strong class="block text-gray-700">Average {{ $key }}</strong>
                    <span class="text-indigo-600 font-semibold">{{ $value }}</span>
                </div>
            @endforeach
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAverages', false)">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
