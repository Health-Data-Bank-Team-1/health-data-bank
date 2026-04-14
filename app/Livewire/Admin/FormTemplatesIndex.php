<?php

namespace App\Livewire\Admin;

use App\Exceptions\WorkflowException;
use App\Models\FormTemplate;
use App\Services\FormTemplateApprovalService;
use Livewire\Component;
use Livewire\WithPagination;

class FormTemplatesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $approvalStatus = '';

    public int $perPage = 15;

    // reject modal state
    public bool $showRejectModal = false;

    public ?string $rejectTemplateId = null;

    public string $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'approvalStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingApprovalStatus(): void
    {
        $this->resetPage();
    }

    public function approve(string $templateId, FormTemplateApprovalService $service): void
    {
        $template = FormTemplate::findOrFail($templateId);

        try {
            $service->approve($template, auth()->user());
            session()->flash('success', 'Template approved.');
        } catch (WorkflowException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->resetPage();
    }

    public function openReject(string $templateId): void
    {
        $this->rejectTemplateId = $templateId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function reject(FormTemplateApprovalService $service): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'max:255'],
            'rejectTemplateId' => ['required'],
        ]);

        $template = FormTemplate::findOrFail($this->rejectTemplateId);

        try {
            $service->reject($template, auth()->user(), $this->rejectReason);
            session()->flash('success', 'Template rejected.');
        } catch (WorkflowException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->showRejectModal = false;
        $this->rejectTemplateId = null;
        $this->rejectReason = '';

        $this->resetPage();
    }

    public function render()
    {
        $query = FormTemplate::query();

        if ($this->approvalStatus !== '') {
            $query->where('approval_status', $this->approvalStatus);
        }

        if ($this->search !== '') {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        $templates = $query
            ->orderByRaw("FIELD(approval_status, 'pending') DESC")
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.form-templates-index', [
            'templates' => $templates,
        ])->layout('layouts.admin');
    }
}
