<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplateVersion extends Model
{
    protected $fillable = [
        'form_template_id',
        'version',
        'title',
        'schema',
        'created_by',
    ];

    protected $casts = [
        'schema' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }
}
