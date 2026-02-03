<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class AggregatedData extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'metrics',
        'anonymization_level',
    ];

    protected $casts = [
        'metrics' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
