<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;

class Report extends Model
{
    use HasUuid, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'researcher_id',
        'report_type',
    ];

    public function researcher()
    {
        return $this->belongsTo(Account::class, 'researcher_id');
    }

    public function aggregatedData()
    {
        return $this->hasMany(AggregatedData::class);
    }
}
