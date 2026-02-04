<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Account extends Model
{
    use HasUuid;

    protected $fillable = [
        'account_type',
        'name',
        'email',
        'status',
    ];

    public function credentials()
    {
        return $this->hasOne(AuthenticationCredential::class);
    }

    public function twoFactorMethods()
    {
        return $this->hasMany(TwoFactorMethod::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'account_roles');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function healthEntries()
    {
        return $this->hasMany(HealthEntry::class);
    }

    public function dashboard()
    {
        return $this->hasOne(Dashboard::class);
    }

    public function healthGoals()
    {
        return $this->hasMany(HealthGoal::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'researcher_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }
}
