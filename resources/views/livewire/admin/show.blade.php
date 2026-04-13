<x-admin-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            Form Review Details
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="mb-6">
                    <a
                        href="{{ url()->previous() }}"
                        class="text-sm text-indigo-600 hover:underline"
                    >
                        ← Back to Form Review
                    </a>
                </div>

                <div class="space-y-3">
                    <p><strong>Title:</strong> {{ $template->title }}</p>
                    <p><strong>Description:</strong> {{ $template->description ?? 'N/A' }}</p>
                    <p><strong>Purpose:</strong> {{ $template->purpose ?? 'N/A' }}</p>
                    <p><strong>Version:</strong> {{ $template->version }}</p>
                    <p><strong>Status:</strong> {{ $template->approval_status }}</p>
                    <p><strong>Created:</strong> {{ $template->created_at?->format('D, M j, Y g:i A') }}</p>
                </div>

                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Fields</h2>

                    @if($template->fields->count())
                        <div class="col-span-6 sm:col-span-4">
                            @foreach($template->fields as $field)
                                @php
                                    $rules = is_string($field->validation_rules)
                                        ? json_decode($field->validation_rules, true) ?: []
                                        : (array) ($field->validation_rules ?? []);
                                @endphp

                                <div class="py-2">
                                    <x-label for="{{ $field->id }}" value="{{ $field->label }}" />

                                    @if ($field->field_type === 'Text')
                                        <x-input id="{{ $field->id }}" class="mt-1 block w-full" type="text" disabled />
                                    @elseif ($field->field_type === 'RadioButton')
                                        <div class="mt-2 space-y-2">
                                            @foreach ($field->options as $option)
                                                <label class="flex items-center space-x-4 mt-1">
                                                    <input type="radio" value="{{ $option }}" disabled
                                                        class="h-4 w-4 mr-2 text-indigo-600 border-gray-300 focus:ring-indigo-500 rounded">
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($field->field_type === 'Checkbox')
                                        <div class="mt-2 space-y-2">
                                            @foreach ($field->options as $option)
                                                <label class="flex items-center space-x-4 mt-1">
                                                    <input type="checkbox" value="{{ $option }}" disabled
                                                        class="h-4 w-4 mr-2 text-indigo-600 border-gray-300 focus:ring-indigo-500 rounded">
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif ($field->field_type === 'Date')
                                        <x-input id="{{ $field->id }}" class="mt-1 block w-full" type="date" disabled />
                                    @elseif ($field->field_type === 'Number')
                                        <x-input id="{{ $field->id }}" class="mt-1 block w-full" type="number" disabled />
                                    @endif

                                    @if($field->help_text)
                                        <p class="text-sm text-gray-500 mt-1">{{ $field->help_text }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No fields found for this form.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
