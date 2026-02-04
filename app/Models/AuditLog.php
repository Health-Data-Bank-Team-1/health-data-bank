<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action_type',
        'timestamp',
    ];

    public function actor()
    {
        return $this->belongsTo(Account::class, 'actor_id');
    }
}
