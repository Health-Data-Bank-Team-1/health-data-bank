<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'message',
        'link',
        'status',
    ];
}
