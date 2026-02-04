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
        'field_type',
        'validation_rules',
    ];

    protected $casts = [
        'validation_rules' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }
}
