<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
}
