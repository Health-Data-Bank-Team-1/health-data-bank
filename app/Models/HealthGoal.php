<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class HealthGoal extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'target_value',
        'start_date',
        'end_date',
        'status',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
