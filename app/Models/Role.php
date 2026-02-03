<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Role extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'role_name',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
}
