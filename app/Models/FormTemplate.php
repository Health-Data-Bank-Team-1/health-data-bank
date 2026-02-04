<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class FormTemplate extends Model
{
    use HasUuid;

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
