<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectApproval extends Model
{
    use Auditable;
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
        'is_revoked',
        'revoked_by',
        'revoked_at',
        'revocation_reason',
    ];

    protected $casts = [
        'action_taken_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_revoked' => 'boolean',
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
     * Get the user who revoked this approval
     */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Scope for active (non-revoked) approvals
     */
    public function scopeActive($query)
    {
        return $query->where('is_revoked', false);
    }

    /**
     * Scope for revoked approvals
     */
    public function scopeRevoked($query)
    {
        return $query->where('is_revoked', true);
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
     * Get the display name for the level (RA 7160 LGU Hierarchy)
     */
    public function getLevelDisplayAttribute(): string
    {
        return match ($this->level) {
            'barangay' => 'Barangay Development Council',
            'municipal' => 'Municipal Planning & Development Office (MPDO)',
            'provincial' => 'Provincial Planning & Development Office (PPDO)',
            'governor' => 'Office of the Provincial Governor',
            default => ucfirst($this->level),
        };
    }
}
