<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class ProviderFeedback extends Model
{
    use HasUuid;

    protected $table = 'provider_feedback';

    protected $fillable = [
        'patient_account_id',
        'provider_account_id',
        'feedback',
        'recommended_actions',
    ];

    public function patient()
    {
        return $this->belongsTo(Account::class, 'patient_account_id');
    }

    public function provider()
    {
        return $this->belongsTo(Account::class, 'provider_account_id');
    }
}