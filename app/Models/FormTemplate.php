<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;

class FormTemplate extends Model
{
    use HasUuid, HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'version',
        'status',
        'description',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}
