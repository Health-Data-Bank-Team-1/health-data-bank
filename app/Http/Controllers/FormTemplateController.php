<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    public function store(Request $request)
    {
        $template = FormTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'version' => 1,
            'status' => 'active',
            'approval_status' => 'draft',
        ]);

        return response()->json($template);
    }

    public function update(Request $request, FormTemplate $template)
    {
        //if template is approved, create a new version instead of overwriting
        if ($template->approval_status === 'approved') {

            $newTemplate = $template->replicate();
            $newTemplate->version = $template->version + 1;
            $newTemplate->approval_status = 'draft';
            $newTemplate->save();

            $newTemplate->update($request->only(['name', 'description']));

            return response()->json([
                'message' => 'New version created',
                'template' => $newTemplate
            ]);
        }

        //normal update if not approved
        $template->update($request->only(['name', 'description']));

        return response()->json($template);
    }
}
