<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid;

class Role extends SpatieRole
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';
}
