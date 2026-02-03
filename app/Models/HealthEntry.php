<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class HealthEntry extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'account_id',
        'timestamp',
        'encrypted_values',
    ];

    protected $casts = [
        'encrypted_values' => 'array',
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
