<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ReportUpdate extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_id',
        'researcher_account_id',
        'content',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
