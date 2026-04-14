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
                        <div class="space-y-4">
                            @foreach($template->fields as $field)
                                @php
                                    $rules = json_decode($field->validation_rules ?? '{}', true) ?: [];
                                @endphp

                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p><strong>Label:</strong> {{ $field->label }}</p>
                                    <p><strong>Help Text:</strong> {{ $field->help_text ?: 'N/A' }}</p>
                                    <p><strong>Type:</strong> {{ $field->field_type }}</p>
                                    <p><strong>Required:</strong> {{ $field->is_required ? 'Yes' : 'No' }}</p>
                                    <p><strong>Min:</strong> {{ $rules['min'] ?? 'N/A' }}</p>
                                    <p><strong>Max:</strong> {{ $rules['max'] ?? 'N/A' }}</p>
                                    <p><strong>Order:</strong> {{ $field->display_order }}</p>
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
