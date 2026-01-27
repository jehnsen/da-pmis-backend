<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'download_count',
        'document_type',
        'category_id',
        'department_id',
        'fiscal_year',
        'is_featured',
        'published_date',
        'created_by',
    ];

    protected $casts = [
        'published_date' => 'date',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'fiscal_year' => 'integer',
        'is_featured' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created this document
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the primary category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get categories for this document (many-to-many)
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(DocumentCategory::class, 'document_category_pivot')
            ->withTimestamps();
    }

    /**
     * Get file size in MB
     */
    public function getFileSizeMbAttribute(): ?float
    {
        if ($this->file_size) {
            return round($this->file_size / (1024 * 1024), 2);
        }

        return null;
    }

    /**
     * Get the full file URL
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    /**
     * Scope for featured documents
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for filtering by document type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope for filtering by fiscal year
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }
}
