<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasUuid;

class Permission extends SpatiePermission
{
    use HasUuid;

    protected $table = 'permissions';
    protected $fillable = ['permission_name', 'scope'];
    public $timestamps = false;

    protected $guard_name = null;

    public function getGuardName()
    {
        return null;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Role::class,
            'role_permissions',
            'permission_id',
            'role_id'
        );
    }
}
