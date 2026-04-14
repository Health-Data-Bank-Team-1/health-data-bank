<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class FormField extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'form_template_id',
        'label',
        'help_text',
        'metric_key',
        'field_type',
        'validation_rules',
        'goal_enabled',
        'options',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'options' => 'array',
        'goal_enabled' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }
}
