<?php

namespace App\Livewire\Researcher;

use App\Models\FormTemplate;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResearcherForms extends Component
{
    public $forms = [];
    public bool $showForm = false;
    public ?string $editingTemplateId = null;

    public string $title = '';
    public string $description = '';
    public string $purpose = '';
    public int $version = 1;
    public string $approval_status = 'pending';

    public array $fields = [];

    public function mount(): void
    {
        $this->loadForms();
    }

    public function loadForms(): void
    {
        $this->forms = FormTemplate::query()
            ->orderByDesc('created_at')
            ->get();
    }

    public function createForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->addField();
    }

    public function addField(): void
    {
        $this->fields[] = [
            'label' => '',
            'help_text' => '',
            'type' => 'text',
            'required' => false,
            'min' => null,
            'max' => null,
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    public function editForm(string $templateId): void
    {
        $template = FormTemplate::with('fields')->findOrFail($templateId);

        AuditLogger::log(
            'form_template_edit_view',
            ['form', 'outcome:success'],
            Auth::user(),
            [
                'target_type' => 'form_template',
                'target_id' => $template->id,
            ],
            []
        );

        $this->editingTemplateId = $template->id;
        $this->title = $template->title ?? '';
        $this->description = $template->description ?? '';
        $this->purpose = $template->purpose ?? '';
        $this->version = $template->version ?? 1;
        $this->approval_status = $template->approval_status ?? 'draft';

        $this->fields = $template->fields
            ->sortBy('display_order')
            ->map(function ($field) {
                $rules = json_decode($field->validation_rules ?? '{}', true) ?: [];

                return [
                    'label' => $field->label ?? '',
                    'help_text' => $field->help_text ?? '',
                    'type' => $field->field_type ?? 'text',
                    'required' => (bool) $field->is_required,
                    'min' => $rules['min'] ?? null,
                    'max' => $rules['max'] ?? null,
                ];
            })
            ->values()
            ->toArray();

        if (empty($this->fields)) {
            $this->addField();
        }

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->purpose = '';
        $this->version = 1;
        $this->approval_status = 'pending';
        $this->fields = [];
        $this->editingTemplateId = null;
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.help_text' => 'nullable|string|max:255',
            'fields.*.type' => 'required|in:text,textarea,number,date,dropdown,checkbox',
            'fields.*.required' => 'required|boolean',
            'fields.*.min' => 'nullable|numeric',
            'fields.*.max' => 'nullable|numeric',
        ];
    }

    protected function validateFieldRanges(): void
    {
        foreach ($this->fields as $index => $field) {
            if (
                $field['min'] !== null &&
                $field['max'] !== null &&
                is_numeric($field['min']) &&
                is_numeric($field['max']) &&
                $field['max'] < $field['min']
            ) {
                $this->addError("fields.$index.max", 'The max value must be greater than or equal to the min value.');
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], [])
            );
        }
    }

    public function saveDraft(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'purpose' => 'nullable|string',
        ]);

        $isNew = ! $this->editingTemplateId;

        $template = FormTemplate::updateOrCreate(
            ['id' => $this->editingTemplateId],
            [
                'title' => $this->title,
                'description' => $this->description,
                'purpose' => $this->purpose,
                'version' => $this->version,
                'approval_status' => 'draft',

                // Ensure schema is always set (DB requires it)
                'schema' => json_encode(['fields' => []]),
            ]
        );

        $this->editingTemplateId = $template->id;
        $this->approval_status = 'draft';
        $this->loadForms();

        AuditLogger::log(
            $isNew ? 'form_template_created' : 'form_template_updated',
            ['form', 'outcome:success'],
            Auth::user(),
            [
                'target_type' => 'form_template',
                'target_id' => $template->id,
            ],
            [
                'status' => 'draft',
            ]
        );

        session()->flash('success', 'Form draft saved successfully.');
    }

    public function submitForApproval(): void
    {
        $this->validate();
        $this->validateFieldRanges();

        $template = FormTemplate::updateOrCreate(
            ['id' => $this->editingTemplateId],
            [
                'title' => $this->title,
                'description' => $this->description,
                'purpose' => $this->purpose,
                'version' => $this->version,
                'approval_status' => 'pending',

                // Ensure schema is always set (DB requires it)
                'schema' => json_encode(['fields' => []]),
            ]
        );

        $template->fields()->delete();

        foreach ($this->fields as $index => $field) {
            $template->fields()->create([
                'label' => $field['label'],
                'help_text' => $field['help_text'],
                'field_type' => $field['type'],
                'is_required' => (bool) $field['required'],
                'validation_rules' => json_encode([
                    'min' => $field['min'],
                    'max' => $field['max'],
                ]),
                'display_order' => $index + 1,
            ]);
        }

        AuditLogger::log(
            'form_template_submitted',
            ['form', 'outcome:success'],
            Auth::user(),
            [
                'target_type' => 'form_template',
                'target_id' => $template->id,
            ],
            [
                'field_count' => count($this->fields),
                'status' => 'pending',
            ]
        );

        $this->loadForms();
        $this->resetForm();
        $this->showForm = false;

        session()->flash('success', 'Form submitted for approval successfully.');
    }

    public function deleteForm(string $id): void
    {
        $form = FormTemplate::findOrFail($id);

        if ($form->approval_status === 'approved') {
            session()->flash('message', 'Approved forms cannot be deleted.');
            return;
        }

        $form->delete();

        $this->forms = FormTemplate::latest()->get();

        session()->flash('message', 'Form deleted successfully.');
    }

    public function render()
    {
        return view('livewire.researcher.forms')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Forms',
            ]);
    }
}