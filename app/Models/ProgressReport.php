<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'department_id',
        'report_period',
        'reporting_date',
        'physical_progress',
        'financial_progress',
        'summary',
        'accomplishments',
        'issues',
        'risks',
        'next_steps',
        'milestones_completed',
        'progress_status',
        'created_by',
    ];

    protected $casts = [
        'reporting_date' => 'date',
        'physical_progress' => 'integer',
        'financial_progress' => 'integer',
        'milestones_completed' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Progress status labels
     */
    public const PROGRESS_STATUSES = [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'delayed' => 'Delayed',
        'ahead' => 'Ahead of Schedule',
    ];

    /**
     * Get the project this report belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the department that owns this report
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who created this report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get metrics for this report
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ReportMetric::class);
    }

    /**
     * Get images for this report
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProgressReportImage::class)->orderBy('display_order')->orderBy('id');
    }

    /**
     * Scope for filtering by project
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope for filtering by department
     */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope for filtering by period
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('report_period', $period);
    }

    /**
     * Scope for filtering by progress status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('progress_status', $status);
    }

    /**
     * Scope for reports with issues
     */
    public function scopeWithIssues($query)
    {
        return $query->whereNotNull('issues')->where('issues', '!=', '');
    }

    /**
     * Scope for reports with risks
     */
    public function scopeWithRisks($query)
    {
        return $query->whereNotNull('risks')->where('risks', '!=', '');
    }

    /**
     * Get the variance between physical and financial progress
     */
    public function getVarianceAttribute(): int
    {
        return $this->physical_progress - $this->financial_progress;
    }

    /**
     * Get progress status display name
     */
    public function getProgressStatusDisplayAttribute(): string
    {
        return self::PROGRESS_STATUSES[$this->progress_status] ?? ucfirst(str_replace('_', ' ', $this->progress_status));
    }

    /**
     * Check if report is on track
     */
    public function isOnTrack(): bool
    {
        return $this->progress_status === 'on_track';
    }

    /**
     * Check if report indicates delay
     */
    public function isDelayed(): bool
    {
        return $this->progress_status === 'delayed';
    }

    /**
     * Check if report indicates risk
     */
    public function isAtRisk(): bool
    {
        return $this->progress_status === 'at_risk';
    }

    /**
     * Get completed milestones count
     */
    public function getCompletedMilestonesCountAttribute(): int
    {
        return is_array($this->milestones_completed) ? count($this->milestones_completed) : 0;
    }

    /**
     * Auto-determine progress status based on variance
     */
    public function determineProgressStatus(): string
    {
        $variance = $this->variance;

        // Ahead if physical progress significantly exceeds financial progress
        if ($variance > 10) {
            return 'ahead';
        }

        // Delayed if financial progress significantly exceeds physical progress
        if ($variance < -15) {
            return 'delayed';
        }

        // At risk if there's moderate negative variance or issues/risks reported
        if ($variance < -5 || ! empty($this->issues) || ! empty($this->risks)) {
            return 'at_risk';
        }

        return 'on_track';
    }
}
