<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class TwoFactorMethod extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'method_type',
        'secret_key',
        'enabled',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
