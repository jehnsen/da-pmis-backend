<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApproval extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'action',
        'comments',
        'reason',
        'level',
        'from_status',
        'to_status',
        'action_taken_at',
    ];

    protected $casts = [
        'action_taken_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project this approval belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who took this action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by action type
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by level
     */
    public function scopeOfLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for approved actions
     */
    public function scopeApproved($query)
    {
        return $query->where('action', 'approved');
    }

    /**
     * Scope for rejected actions
     */
    public function scopeRejected($query)
    {
        return $query->where('action', 'rejected');
    }

    /**
     * Get the display name for the action
     */
    public function getActionDisplayAttribute(): string
    {
        return match ($this->action) {
            'submitted' => 'Submitted for Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'requested_changes' => 'Changes Requested',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get the display name for the level
     */
    public function getLevelDisplayAttribute(): string
    {
        return match ($this->level) {
            'field' => 'Field Officer',
            'municipal' => 'Municipal Officer',
            'provincial' => 'Provincial Officer',
            'regional' => 'Regional Director',
            default => ucfirst($this->level),
        };
    }
}
