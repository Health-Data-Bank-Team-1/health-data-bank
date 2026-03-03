<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Casts\EncryptedArray;

class HealthEntry extends Model
{
    use HasUuid, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'account_id',
        'timestamp',
        'encrypted_values',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'encrypted_values' => EncryptedArray::class,
    ];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}