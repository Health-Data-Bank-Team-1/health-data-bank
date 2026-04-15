<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSubmission extends Model
{
    use HasUuid, HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'form_template_id',
        'status',
        'submitted_at',
        'flag_reason',
        'flagged_by',
        'flagged_at',
        'deleted_by',
        'deletion_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'flagged_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function healthEntries()
    {
        return $this->hasMany(HealthEntry::class, 'submission_id');
    }
}
