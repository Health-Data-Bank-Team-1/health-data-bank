<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\HasUuid;

class Role extends Model
{
    use HasUuid, HasRoles;

    protected $table = 'roles';
    protected $fillable = ['role_name'];
    public $timestamps = false;

    protected $guard_name = null;      // disable Spatie guard

    public function getGuardName()
    {
        return null;
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'account_roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',   // your pivot table
            'role_id',            // foreign key in pivot pointing to this role
            'permission_id'       // foreign key in pivot pointing to permission
        );
    }
}
