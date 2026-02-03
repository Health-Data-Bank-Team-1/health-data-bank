<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Dashboard extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
