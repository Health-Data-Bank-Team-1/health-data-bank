<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUuid;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class FormTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'id',
        'title',
        'schema',
        'version',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $attributes = [
        'version' => 1,
        'approval_status' => 'pending',
    ];

    protected $casts = [
        'schema' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //Relationships
    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions()
    {
        return $this->hasMany(FormTemplateVersion::class);
    }
}
