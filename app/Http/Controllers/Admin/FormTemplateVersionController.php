<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormTemplateVersionController extends Controller
{
    //GET version history
    public function index(FormTemplate $template)
    {
        return $template->versions()
            ->orderByDesc('version')
            ->get();
    }

    //POST rollback
    public function rollback(FormTemplate $template, int $version)
    {
        return DB::transaction(function () use ($template, $version) {

            $template = $template->fresh(); //ensure we have the latest version number

            //prevent invalid rollback
            if ($version >= $template->version) {
                return response()->json([
                    'message' => 'Cannot rollback to current or future version.'
                ], 422);
            }

            $snapshot = $template->versions()
                ->where('version', $version)
                ->first();

            if (!$snapshot) {
                return response()->json([
                    'message' => 'Version not found.'
                ], 404);
            }

            //save current state as a snapshot before rollback
            FormTemplateVersion::create([
                'form_template_id' => $template->id,
                'version' => $template->version,
                'title' => $template->title,
                'schema' => $template->schema,
                'created_by' => auth()->id(),
            ]);

            //restore snapshot without firing observers
            \App\Models\FormTemplate::withoutEvents(function () use ($template, $snapshot, $version) {
                $template->update([
                    'title' => $snapshot->title,
                    'schema' => $snapshot->schema,
                    'version' => $version,
                ]);
            });

            return response()->json([
                'message' => 'Rollback successful',
                'new_version' => $version,
            ]);
        });
    }

}
