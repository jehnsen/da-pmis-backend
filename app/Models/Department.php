<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'description'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get projects under this department
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get KPIs for this department
     */
    public function kpis(): HasMany
    {
        return $this->hasMany(DepartmentKpi::class);
    }

    /**
     * Get progress reports for this department
     */
    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class);
    }

    /**
     * Get funding distributions for this department
     */
    public function fundingDistributions(): HasMany
    {
        return $this->hasMany(FundingDistribution::class);
    }

    /**
     * Get users in this department
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
