<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Permission extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'permission_name',
        'scope',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
