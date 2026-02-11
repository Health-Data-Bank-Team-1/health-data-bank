<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Services\FormTemplateApprovalService;
use Illuminate\Http\Request;

class FormTemplateApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin']); // Uses UUID roles
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

    public function reject(Request $request, FormTemplate $template, FormTemplateApprovalService $service)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $service->reject($template, auth()->user(), $request->reason);
        return response()->json(['message' => 'Template rejected']);
    }
}
