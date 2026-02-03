<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class FormSubmission extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'form_template_id',
        'status',
        'submitted_at',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function template()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function healthEntries()
    {
        return $this->hasMany(HealthEntry::class, 'submission_id');
    }
}
