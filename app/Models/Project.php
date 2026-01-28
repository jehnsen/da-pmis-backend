<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'project_type_id',
        'project_status_id',
        'approval_status',
        'submitted_by',
        'submitted_at',
        'budget',
        'start_date',
        'end_date',
        'location_lat',
        'location_lng',
        'is_public',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'is_public' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the department that owns the project
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the project type
     */
    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    /**
     * Get the project status
     */
    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class);
    }

    /**
     * Get the user who submitted the project for approval
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get team members for this project
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get project team member records
     */
    public function projectTeamMembers(): HasMany
    {
        return $this->hasMany(ProjectTeamMember::class);
    }

    /**
     * Get milestones for this project
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    /**
     * Get funding distributions for this project
     */
    public function fundingDistributions(): HasMany
    {
        return $this->hasMany(FundingDistribution::class);
    }

    /**
     * Get approval history for this project
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(ProjectApproval::class)->orderBy('action_taken_at', 'desc');
    }

    /**
     * Get disbursements for this project
     */
    public function disbursements(): HasMany
    {
        return $this->hasMany(ProjectDisbursement::class)->orderBy('disbursement_date', 'desc');
    }

    /**
     * Get images for this project
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('display_order')->orderBy('id');
    }

    /**
     * Get documents for this project
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->orderBy('published_date', 'desc');
    }

    /**
     * Get audit logs for this project
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'model_id')
            ->where('model_type', self::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get total disbursed amount
     */
    public function getTotalDisbursedAttribute(): float
    {
        return (float) $this->disbursements()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudgetAttribute(): float
    {
        return (float) $this->budget - $this->total_disbursed;
    }

    /**
     * Get budget utilization rate (percentage)
     */
    public function getUtilizationRateAttribute(): float
    {
        if ($this->budget <= 0) {
            return 0;
        }

        return round(($this->total_disbursed / $this->budget) * 100, 2);
    }

    /**
     * Scope for public projects
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for internal projects
     */
    public function scopeInternal($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope for draft projects
     */
    public function scopeDraft($query)
    {
        return $query->where('approval_status', 'draft');
    }

    /**
     * Scope for approved projects
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope for pending approval projects
     */
    public function scopePendingApproval($query)
    {
        return $query->whereIn('approval_status', [
            'pending_municipal',
            'pending_provincial',
            'pending_regional',
        ]);
    }

    /**
     * Scope for projects pending at a specific level
     */
    public function scopePendingAt($query, string $level)
    {
        return $query->where('approval_status', "pending_{$level}");
    }

    /**
     * Check if project is in draft status
     */
    public function isDraft(): bool
    {
        return $this->approval_status === 'draft';
    }

    /**
     * Check if project is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if project is rejected
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Check if project is pending approval
     */
    public function isPendingApproval(): bool
    {
        return in_array($this->approval_status, [
            'pending_municipal',
            'pending_provincial',
            'pending_regional',
        ]);
    }

    /**
     * Get the current pending level
     */
    public function getCurrentPendingLevel(): ?string
    {
        return match ($this->approval_status) {
            'pending_municipal' => 'municipal',
            'pending_provincial' => 'provincial',
            'pending_regional' => 'regional',
            default => null,
        };
    }

    /**
     * Get display name for approval status
     */
    public function getApprovalStatusDisplayAttribute(): string
    {
        return match ($this->approval_status) {
            'draft' => 'Draft',
            'pending_municipal' => 'Pending Municipal Approval',
            'pending_provincial' => 'Pending Provincial Approval',
            'pending_regional' => 'Pending Regional Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->approval_status ?? 'draft')),
        };
    }
}
