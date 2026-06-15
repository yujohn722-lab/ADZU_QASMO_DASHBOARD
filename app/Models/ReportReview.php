<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReportReview extends Model
{
    protected $fillable = [
        'module_key',
        'module_label',
        'reportable_type',
        'reportable_id',
        'respondent_id',
        'status',
        'admin_message',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ReportNotification::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'changes_requested' => 'Changes Requested',
            default => 'Pending Review',
        };
    }
}
