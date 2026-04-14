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
                        <div class="space-y-6">
                            @foreach($template->fields as $field)
                                @php
                                    $rules = is_string($field->validation_rules)
                                        ? json_decode($field->validation_rules, true) ?: []
                                        : (array) ($field->validation_rules ?? []);

                                    $options = is_array($field->options) ? $field->options : [];
                                    $fieldType = strtolower(trim($field->field_type ?? ''));
                                @endphp

                                <div>
                                    <label for="field-{{ $field->id }}" class="block text-sm font-medium text-gray-700">
                                        {{ $field->label }}
                                        @if(!empty($field->is_required))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    @if($field->help_text)
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ $field->help_text }}
                                        </p>
                                    @endif

                                    @if ($fieldType === 'text')
                                        <input
                                            id="field-{{ $field->id }}"
                                            type="text"
                                            disabled
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        >

                                    @elseif ($fieldType === 'textarea')
                                        <textarea
                                            id="field-{{ $field->id }}"
                                            rows="3"
                                            disabled
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        ></textarea>

                                    @elseif ($fieldType === 'number')
                                        <input
                                            id="field-{{ $field->id }}"
                                            type="number"
                                            disabled
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        >

                                    @elseif ($fieldType === 'date')
                                        <input
                                            id="field-{{ $field->id }}"
                                            type="date"
                                            disabled
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        >

                                    @elseif ($fieldType === 'dropdown')
                                        <select
                                            id="field-{{ $field->id }}"
                                            disabled
                                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50"
                                        >
                                            <option>Select an option</option>
                                            @foreach ($options as $option)
                                                <option>{{ $option }}</option>
                                            @endforeach
                                        </select>

                                    @elseif ($fieldType === 'checkbox')
                                        @if(count($options))
                                            <div class="mt-2 space-y-2 rounded-md border border-gray-300 bg-gray-50 p-3">
                                                @foreach ($options as $option)
                                                    <label class="flex items-center gap-2 text-sm text-gray-700">
                                                        <input
                                                            type="checkbox"
                                                            disabled
                                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        >
                                                        <span>{{ $option }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="mt-2 rounded-md border border-gray-300 bg-gray-50 p-3 text-sm text-gray-500">
                                                Checkbox field
                                            </div>
                                        @endif

                                    @else
                                        <div class="mt-2 rounded-md border border-gray-300 bg-gray-50 p-3 text-sm text-gray-500">
                                            Preview unavailable for field type: {{ $field->field_type }}
                                        </div>
                                    @endif

                                    @if(($rules['min'] ?? null) !== null || ($rules['max'] ?? null) !== null)
                                        <div class="mt-2 text-xs text-gray-500">
                                            @if(($rules['min'] ?? null) !== null)
                                                <span>Min: {{ $rules['min'] }}</span>
                                            @endif

                                            @if(($rules['min'] ?? null) !== null && ($rules['max'] ?? null) !== null)
                                                <span class="mx-1">|</span>
                                            @endif

                                            @if(($rules['max'] ?? null) !== null)
                                                <span>Max: {{ $rules['max'] }}</span>
                                            @endif
                                        </div>
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
