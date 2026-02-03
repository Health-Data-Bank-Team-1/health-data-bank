<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthenticationCredential extends Model
{
    protected $table = 'authentication_credentials';

    protected $primaryKey = 'account_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'password_hash',
        'last_login',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
