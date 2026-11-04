<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SystemDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_code',
        'title',
        'slug',
        'description',
        'category',
        'classification',
        'current_version',
        'file_path',
        'original_filename',
        'stored_filename',
        'mime_type',
        'file_size',
        'file_extension',
        'status',
        'effective_date',
        'review_date',
        'expiry_date',
        'owner_id',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'requires_acknowledgement',
        'is_downloadable',
        'is_active',
        'download_count',
        'view_count',
        'sort_order',
        'review_state',
'last_reviewed_at',
'last_reviewed_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'review_date' => 'date',
        'expiry_date' => 'date',

        'approved_at' => 'datetime',

        'requires_acknowledgement' => 'boolean',
        'is_downloadable' => 'boolean',
        'is_active' => 'boolean',

        'download_count' => 'integer',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'file_size' => 'integer',
        'last_reviewed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id'
        );
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function versions(): HasMany
    {
        return $this->hasMany(
            SystemDocumentVersion::class
        )->orderByDesc('created_at');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(
            SystemDocumentAcknowledgement::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeCategory(
        Builder $query,
        ?string $category
    ): Builder {
        if (!$category) {
            return $query;
        }

        return $query->where(
            'category',
            $category
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function requiresAcknowledgement(): bool
    {
        return (bool) $this->requires_acknowledgement;
    }

    public function hasBeenAcknowledgedBy(
        User $user
    ): bool {
        return $this->acknowledgements()
            ->where('user_id', $user->id)
            ->where(
                'document_version',
                $this->current_version
            )
            ->exists();
    }

public function audiences(): HasMany
{
    return $this->hasMany(
        SystemDocumentAudience::class,
        'system_document_id'
    );
}

/**
 * Determine whether this document is classified as restricted.
 */
public function isRestricted(): bool
{
    return $this->classification === 'restricted';
}


/**
 * Determine whether this document is due for review
 * within the specified number of days.
 */
public function isDueForReview(int $days = 30): bool
{
    if (!$this->review_date) {
        return false;
    }

    return $this->review_date->between(
        now()->startOfDay(),
        now()->addDays($days)->endOfDay()
    );
}


/**
 * Determine whether the document review date has passed.
 */
public function isReviewOverdue(): bool
{
    if (!$this->review_date) {
        return false;
    }

    return $this->review_date->isBefore(
        now()->startOfDay()
    );
}
public function reviews(): HasMany
{
    return $this->hasMany(
        SystemDocumentReview::class,
        'system_document_id'
    )->orderByDesc('reviewed_at');
}
public function lastReviewedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'last_reviewed_by'
    );
}
}
