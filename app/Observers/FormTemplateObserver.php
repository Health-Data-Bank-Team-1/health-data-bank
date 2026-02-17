<?php

namespace App\Observers;

use App\Models\FormTemplate;
use Illuminate\Support\Facades\DB;

class FormTemplateObserver
{
    public function updating(FormTemplate $template): void
    {
        // if someone manually touches version, don't auto-version again
        if ($template->isDirty('version')) {
            return;
        }

        if ($template->isDirty('schema') || $template->isDirty('title')) {
            DB::table('form_template_versions')->insert([
                'form_template_id' => $template->id,
                'title' => $template->getOriginal('title'),
                'schema' => json_encode($template->getOriginal('schema')),
                'version' => $template->version,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $template->version += 1;
        }
    }
}
