<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;

class Dashboard extends Model
{
    use HasUuid, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
