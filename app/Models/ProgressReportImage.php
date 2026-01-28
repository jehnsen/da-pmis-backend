<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressReportImage extends Model
{
    protected $fillable = [
        'progress_report_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'caption',
        'image_type',
        'display_order',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Image type labels
     */
    public const IMAGE_TYPES = [
        'before' => 'Before Photo',
        'after' => 'After Photo',
        'during' => 'During Work',
        'result' => 'Result/Completion',
        'documentation' => 'Documentation',
        'other' => 'Other',
    ];

    /**
     * Get the progress report this image belongs to
     */
    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    /**
     * Get the user who uploaded this image
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope for filtering by image type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('image_type', $type);
    }

    /**
     * Scope for before/after photos
     */
    public function scopeBeforeAfter($query)
    {
        return $query->whereIn('image_type', ['before', 'after']);
    }

    /**
     * Scope for ordering by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    /**
     * Get the image type display name
     */
    public function getImageTypeDisplayAttribute(): string
    {
        return self::IMAGE_TYPES[$this->image_type] ?? ucfirst(str_replace('_', ' ', $this->image_type));
    }

    /**
     * Get the full URL to the image
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 2) . ' KB';
        } else {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
    }
}
