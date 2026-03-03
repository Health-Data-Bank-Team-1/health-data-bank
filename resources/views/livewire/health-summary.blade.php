<div>
    <div>
        <input type="date" wire:model="from">
        @error('from')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <input type="date" wire:model="to">
        @error('to')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>


    <button wire:click="loadSummary">
        Load Summary
    </button>

    @foreach ($avgs as $key => $value)
        <div>
            <strong>{{ $key }}</strong>: {{ $value }}
        </div>
    @endforeach
