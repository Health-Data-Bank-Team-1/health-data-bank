<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderSetting extends Model
{
    protected $fillable = [
        'account_id',
        'frequency',
        'is_active',
        'next_run_at',
        'last_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'next_run_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];
}
