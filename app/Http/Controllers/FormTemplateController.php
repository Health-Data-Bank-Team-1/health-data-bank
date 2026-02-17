<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'schema' => ['required', 'array'],
            'description' => ['nullable', 'string'],
        ]);

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

    public function update(Request $request, FormTemplate $template)
    {

        if (in_array($template->approval_status, ['approved', 'rejected'], true)) {
            $template->update([
                'approval_status' => 'draft',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => null,
            ]);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'schema' => ['sometimes', 'array'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $template->update($validated);

        return response()->json($template);
    }
}

