<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasUuid, HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'researcher_id',
        'report_type',
        'is_archived',
        'archive_reason',
        'archived_by',
        'archived_at',
        'deleted_by',
        'deletion_reason',
        'restored_by',
        'restoration_reason',
        'restored_at',
        'moderation_status',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
        'is_approved',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_approved' => 'boolean',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
        'moderated_at' => 'datetime',
    ];

    public function researcher()
    {
        return $this->belongsTo(Account::class, 'researcher_id');
    }

    public function aggregatedData()
    {
        return $this->hasMany(AggregatedData::class);
    }

    public function updates()
    {
        return $this->hasMany(ReportUpdate::class)->latest();
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    public function moderatedBy()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
