<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class HealthGoal extends Model
{
    use HasUuid;

    protected $fillable = [
        'account_id',
        'metric_key',
        'comparison_operator',
        'target_value',
        'timeframe',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
