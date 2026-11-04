<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentCategory extends Model
{
    protected $table = 'equipment_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Equipment assigned to this category.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(
            Equipment::class,
            'category_id'
        );
    }

    /**
     * Return only active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Display code and category name together.
     */
    public function getFullNameAttribute(): string
    {
        return sprintf(
            '%s - %s',
            $this->code,
            $this->name
        );
    }
}
