@props(['submit'])

<div {{ $attributes->merge(['class' => 'max-w-3xl mx-auto w-full space-y-6']) }}>
    <div class="px-6 py-2 pt-4">
        <h3 class="text-3xl font-medium text-gray-900">
            {{ $title }}
        </h3>

        @if (isset($description) && filled(trim(strip_tags($description))))
            <div class="mt-2 text-sm text-gray-600">
                {{ $description }}
            </div>
        @endif
    </div>

    <form wire:submit="{{ $submit }}">
        <div class="px-4 py-5 sm:p-6 {{ isset($actions) ? 'border-b' : '' }}">
            <div class="grid grid-cols-6 gap-6 pb-4">
                {{ $form }}
            </div>
        </div>

        @if (isset($actions))
            <div class="flex items-center justify-start space-x-2 px-4 py-3 bg-gray-50 sm:px-6">
                {{ $actions }}
            </div>
        @endif
    </form>
</div>
