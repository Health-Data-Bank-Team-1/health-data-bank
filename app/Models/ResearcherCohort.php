<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearcherCohort extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'purpose',
        'filters_json',
        'estimated_size',
        'version',
        'created_by'
    ];

    protected $casts = [
        'filters_json' => 'array',
    ];
}
