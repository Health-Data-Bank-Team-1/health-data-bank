<x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Form Management') }}
    </h1>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-sm text-gray-600 mt-1">
                        View, create, update, preview, and submit form templates for approval.
                    </p>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="mb-4 rounded-md bg-green-100 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Existing Forms</h2>

                    @if(!empty($forms) && count($forms))
                        <div class="space-y-4">
                            @foreach($forms as $form)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-700"><strong>Title:</strong> {{ $form->title }}</p>
                                    <p class="text-sm text-gray-700 mt-2"><strong>Description:</strong> {{ $form->description ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-700 mt-2"><strong>Purpose:</strong> {{ $form->purpose ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-700 mt-2"><strong>Version:</strong> {{ $form->version }}</p>
                                    <p class="text-sm text-gray-700 mt-2"><strong>Status:</strong> {{ $form->approval_status }}</p>

                                    <div class="mt-4 flex gap-2">
                                        <button
                                            wire:click="editForm('{{ $form->id }}')"
                                            class="px-3 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            wire:click="deleteForm('{{ $form->id }}')"
                                            onclick="confirm('Are you sure you want to delete this form?') || event.stopImmediatePropagation()"
                                            class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-500"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No forms available yet.</p>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $editingTemplateId ? 'Edit Form' : 'Create New Form' }}
                        </h2>

                        <button
                            wire:click="createForm"
                            class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                        >
                            Create New Form
                        </button>
                    </div>

                    @if($showForm)
                        <div class="space-y-6">

                            <div class="rounded-lg border border-gray-200 p-4">
                                <h3 class="text-md font-semibold text-gray-900 mb-4">Form Details</h3>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                        <input type="text" wire:model="title" class="w-full rounded-md border-gray-300">
                                        @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <textarea wire:model="description" class="w-full rounded-md border-gray-300"></textarea>
                                        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                                        <textarea wire:model="purpose" class="w-full rounded-md border-gray-300"></textarea>
                                        @error('purpose') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-md font-semibold text-gray-900">Form Fields</h3>

                                    <button
                                        type="button"
                                        wire:click="addField"
                                        class="px-3 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                                    >
                                        + Add Field
                                    </button>
                                </div>

                                @foreach($fields as $index => $field)
                                    <div class="border rounded-lg p-4 mb-4 bg-gray-50">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                                                <input type="text" wire:model="fields.{{ $index }}.label" class="w-full rounded-md border-gray-300">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Help Text</label>
                                                <input type="text" wire:model="fields.{{ $index }}.help_text" class="w-full rounded-md border-gray-300">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                                                <select wire:model="fields.{{ $index }}.type" class="w-full rounded-md border-gray-300">
                                                    <option value="text">Text</option>
                                                    <option value="textarea">Textarea</option>
                                                    <option value="number">Number</option>
                                                    <option value="date">Date</option>
                                                    <option value="dropdown">Dropdown</option>
                                                    <option value="checkbox">Checkbox</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Required</label>
                                                <select wire:model="fields.{{ $index }}.required" class="w-full rounded-md border-gray-300">
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Value</label>
                                                <input type="number" wire:model="fields.{{ $index }}.min" class="w-full rounded-md border-gray-300">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Value</label>
                                                <input type="number" wire:model="fields.{{ $index }}.max" class="w-full rounded-md border-gray-300">
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button
                                                type="button"
                                                wire:click="removeField({{ $index }})"
                                                class="px-3 py-2 bg-rose-500 text-white rounded-md hover:bg-red-700"
                                            >
                                                Remove Field
                                            </button>
                                        </div>
                                        <button
                                            type="button"
                                            wire:click="addField"
                                            class="px-3 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700"
                                        >
                                            + Add Field
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4">
                                <h3 class="text-md font-semibold text-gray-900 mb-4">Preview</h3>

                                <div class="space-y-4">
                                    <p class="text-sm text-gray-700"><strong>Title:</strong> {{ $title ?: 'Untitled Form' }}</p>
                                    <p class="text-sm text-gray-700"><strong>Description:</strong> {{ $description ?: 'No description yet.' }}</p>
                                    <p class="text-sm text-gray-700"><strong>Purpose:</strong> {{ $purpose ?: 'No purpose yet.' }}</p>

                                    @foreach($fields as $field)
                                        <div class="border rounded-md p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                {{ $field['label'] ?: 'Untitled Field' }}
                                                @if(!empty($field['required']))
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>

                                            @if(!empty($field['help_text']))
                                                <p class="text-xs text-gray-500 mb-2">{{ $field['help_text'] }}</p>
                                            @endif

                                            @if(($field['type'] ?? '') === 'textarea')
                                                <textarea class="w-full rounded-md border-gray-300" disabled></textarea>
                                            @elseif(($field['type'] ?? '') === 'dropdown')
                                                <select class="w-full rounded-md border-gray-300" disabled>
                                                    <option>Select an option</option>
                                                </select>
                                            @elseif(($field['type'] ?? '') === 'checkbox')
                                                <input type="checkbox" disabled>
                                            @else
                                                <input
                                                    type="{{ ($field['type'] ?? 'text') === 'number' ? 'number' : (($field['type'] ?? 'text') === 'date' ? 'date' : 'text') }}"
                                                    class="w-full rounded-md border-gray-300"
                                                    disabled
                                                >
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    wire:click="saveDraft"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:border-[#1915014a]"
                                >
                                    Save Draft
                                </button>

                                <button
                                    wire:click="submitForApproval"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-md"
                                >
                                    Submit for Approval
                                </button>

                                <button
                                    wire:click="cancelForm"
                                    class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-gray-600"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">
                            Click <strong>Create New Form</strong> to start building a form.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>