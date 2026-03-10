<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AggregatedData extends Model
{
    use HasUuid, HasFactory;

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
