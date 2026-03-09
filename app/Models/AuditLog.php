<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'action_type',
        'timestamp',
        'route',
        'method',
        'ip',
        'user_agent',
        'meta',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'meta' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(Account::class, 'actor_id');
    }
}
