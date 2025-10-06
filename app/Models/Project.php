<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'project_type_id',
        'project_status_id',
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
}
