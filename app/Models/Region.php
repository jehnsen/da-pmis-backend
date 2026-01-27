<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Region extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code',
        'psgc_code',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get provinces in this region
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    /**
     * Get all municipalities in this region through provinces
     */
    public function municipalities(): HasManyThrough
    {
        return $this->hasManyThrough(Municipality::class, Province::class);
    }

    /**
     * Get crop productions for this region
     */
    public function cropProductions(): HasMany
    {
        return $this->hasMany(CropProduction::class);
    }

    /**
     * Get livestock statistics for this region
     */
    public function livestockStatistics(): HasMany
    {
        return $this->hasMany(LivestockStatistic::class);
    }

    /**
     * Scope for active regions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total province count
     */
    public function getProvinceCountAttribute(): int
    {
        return $this->provinces()->count();
    }

    /**
     * Get total municipality count
     */
    public function getMunicipalityCountAttribute(): int
    {
        return $this->municipalities()->count();
    }
}
