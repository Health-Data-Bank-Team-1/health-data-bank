<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;
use App\Models\ReminderSetting;
use App\Models\Notification;

class Account extends Model
{
    use HasUuid, HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'account_type',
        'name',
        'email',
        'date_of_birth',
        'gender',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_of_birth' => 'date',
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

    public function patients()
    {
        return $this->belongsToMany(
            Account::class,
            'provider_patient',
            'provider_id',
            'patient_id'
        );
    }

    public function providers()
    {
        return $this->belongsToMany(
            Account::class,
            'provider_patient',
            'patient_id',
            'provider_id'
        );
    }

    public function reminderSettings()
    {
        return $this->hasMany(ReminderSetting::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function receivedProviderFeedback()
    {
        return $this->hasMany(ProviderFeedback::class, 'patient_account_id');
    }

    public function submittedProviderFeedback()
    {
        return $this->hasMany(ProviderFeedback::class, 'provider_account_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}
