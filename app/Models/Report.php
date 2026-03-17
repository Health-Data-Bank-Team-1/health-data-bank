<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasUuid, HasFactory, SoftDeletes;

    // Disable timestamps since the reports table doesn't have created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'researcher_id',
        'report_type',
        'moderation_status',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function researcher()
    {
        return $this->belongsTo(Account::class, 'researcher_id');
    }

    public function aggregatedData()
    {
        return $this->hasMany(AggregatedData::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('moderation_status', 'approved')
                     ->whereNull('deleted_at');
    }

    public function scopeArchived($query)
    {
        return $query->where('moderation_status', 'archived')
                     ->orWhereNotNull('deleted_at');
    }

    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    // Accessors
    public function isArchived()
    {
        return $this->moderation_status === 'archived' || $this->deleted_at !== null;
    }

    public function isApproved()
    {
        return $this->moderation_status === 'approved' && $this->deleted_at === null;
    }
}