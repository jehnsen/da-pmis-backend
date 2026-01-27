<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipality extends Model
{
    protected $fillable = [
        'province_id',
        'code',
        'name',
        'psgc_code',
        'type',
        'latitude',
        'longitude',
        'zip_code',
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
     * Municipality types
     */
    public const TYPES = [
        'municipality' => 'Municipality',
        'city' => 'City',
        'component_city' => 'Component City',
        'huc' => 'Highly Urbanized City',
        'icc' => 'Independent Component City',
    ];

    /**
     * Get the province this municipality belongs to
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the region through province
     */
    public function getRegionAttribute()
    {
        return $this->province?->region;
    }

    /**
     * Scope for active municipalities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for municipalities in a province
     */
    public function scopeInProvince($query, int $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }

    /**
     * Scope for cities only
     */
    public function scopeCities($query)
    {
        return $query->whereIn('type', ['city', 'component_city', 'huc', 'icc']);
    }

    /**
     * Scope for municipalities only (non-cities)
     */
    public function scopeMunicipalitiesOnly($query)
    {
        return $query->where('type', 'municipality');
    }

    /**
     * Check if this is a city
     */
    public function isCity(): bool
    {
        return in_array($this->type, ['city', 'component_city', 'huc', 'icc']);
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get full location string
     */
    public function getFullLocationAttribute(): string
    {
        $parts = [$this->name];

        if ($this->province) {
            $parts[] = $this->province->name;

            if ($this->province->region) {
                $parts[] = $this->province->region->name;
            }
        }

        return implode(', ', $parts);
    }
}
