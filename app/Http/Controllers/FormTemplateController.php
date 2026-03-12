<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormTemplateRequest;
use App\Http\Requests\UpdateFormTemplateRequest;
use App\Models\FormTemplate;

class FormTemplateController extends Controller
{
    public function store(StoreFormTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = FormTemplate::create([
            'title' => $validated['title'],
            'schema' => $validated['schema'],
            'description' => $validated['description'] ?? null,
            'version' => 1,
            'approval_status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);

        return response()->json($template, 201);
    }

    public function update(UpdateFormTemplateRequest $request, FormTemplate $template)
    {
        if (in_array($template->approval_status, ['approved', 'rejected'], true)) {
            return response()->json([
                'message' => 'Cannot update a template that has been approved or rejected.',
            ], 422);
        }

        $validated = $request->validated();
        $template->update($validated);

        return response()->json($template);
    }
}