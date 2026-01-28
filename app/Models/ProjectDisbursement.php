<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDisbursement extends Model
{
    use Auditable;
    protected $fillable = [
        'project_id',
        'amount',
        'disbursement_date',
        'category',
        'description',
        'reference_number',
        'vendor_name',
        'receipt_number',
        'notes',
        'approved_by',
        'approved_at',
        'created_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disbursement_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Available disbursement categories
     */
    public const CATEGORIES = [
        'equipment' => 'Equipment',
        'labor' => 'Labor',
        'materials' => 'Materials',
        'supplies' => 'Supplies',
        'services' => 'Services',
        'travel' => 'Travel',
        'training' => 'Training',
        'utilities' => 'Utilities',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    /**
     * Get the project this disbursement belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who approved this disbursement
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this disbursement
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for filtering by category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed disbursements
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending disbursements
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('disbursement_date', [$startDate, $endDate]);
    }

    /**
     * Scope for fiscal year
     */
    public function scopeFiscalYear($query, int $year)
    {
        return $query->whereYear('disbursement_date', $year);
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Check if disbursement is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if disbursement is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if disbursement is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
