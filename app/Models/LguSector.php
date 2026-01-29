<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LGU Sector Model
 *
 * Represents the four main governance sectors under RA 7160:
 * 1. Social Services (SS)
 * 2. Economic Services (ES)
 * 3. Infrastructure & Environmental Management (IEM)
 * 4. General Public Services (GPS)
 */
class LguSector extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'color_code',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get projects under this sector
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'sector_id');
    }

    /**
     * Scope for active sectors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get sector by code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Get total projects count for this sector
     */
    public function getProjectsCountAttribute(): int
    {
        return $this->projects()->count();
    }

    /**
     * Get approved projects count for this sector
     */
    public function getApprovedProjectsCountAttribute(): int
    {
        return $this->projects()->where('approval_status', 'approved')->count();
    }

    /**
     * Get total budget allocated to this sector
     */
    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->projects()->sum('budget');
    }

    /**
     * Get total disbursed amount for this sector
     */
    public function getTotalDisbursedAttribute(): float
    {
        return (float) $this->projects()->get()->sum(function ($project) {
            return $project->total_disbursed;
        });
    }

    /**
     * Get utilization rate for this sector
     */
    public function getUtilizationRateAttribute(): float
    {
        $totalBudget = $this->total_budget;

        if ($totalBudget <= 0) {
            return 0;
        }

        return round(($this->total_disbursed / $totalBudget) * 100, 2);
    }
}
