<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Role extends Model
{
    // Remove HasFactory since we don't need a factory for Role
    // use HasFactory;  

    protected $fillable = ['name'];

    /**
     * Get the users that belong to this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}