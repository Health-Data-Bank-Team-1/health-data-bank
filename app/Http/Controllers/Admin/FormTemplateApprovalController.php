<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectFormTemplateRequest;
use App\Models\FormTemplate;
use App\Services\FormTemplateApprovalService;

class FormTemplateApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function submit(FormTemplate $template, FormTemplateApprovalService $service)
    {
        $service->submitForApproval($template);
        return response()->json(['message' => 'Submitted for approval']);
    }

    public function approve(FormTemplate $template, FormTemplateApprovalService $service)
    {
        $service->approve($template, auth()->user());
        return response()->json(['message' => 'Template approved']);
    }

    public function reject(RejectFormTemplateRequest $request, FormTemplate $template, FormTemplateApprovalService $service)
    {
        $validated = $request->validated();
        $service->reject($template, auth()->user(), $validated['reason']);
        return response()->json(['message' => 'Template rejected']);
    }

    public function show(FormTemplate $template)
    {
        $template->load([
            'fields' => fn ($query) => $query->orderBy('id'),
        ]);

        return view('livewire.admin.show', [
            'template' => $template,
        ]);
    }
}
