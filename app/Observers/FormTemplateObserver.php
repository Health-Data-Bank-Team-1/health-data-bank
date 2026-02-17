<?php

namespace App\Observers;

use App\Models\FormTemplate;
use Illuminate\Support\Facades\DB;

class FormTemplateObserver
{
    public function updating(FormTemplate $template): void
    {
        //if someone manually touches version, don't auto-version again
        if ($template->isDirty('version')) {
            return;
        }

        //only snapshot if meaningful fields changed
        if (!($template->isDirty('schema') || $template->isDirty('title'))) {
            return;
        }

        $originalSchema = $template->getOriginal('schema');

        $schemaJson = is_string($originalSchema)
            ? $originalSchema
            : json_encode($originalSchema);

        DB::table('form_template_versions')->insert([
            'form_template_id' => $template->id,
            'title' => $template->getOriginal('title'),
            'schema' => $schemaJson,
            'version' => $template->version,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //increment version number on the template being saved
        $template->version = ($template->version ?? 1) + 1;
    }
}
