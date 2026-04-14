<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeseriesData extends Model
{
    use HasFactory, HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'metric',
        'bucket',
        'points',
    ];

    protected $casts = [
        'points' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
