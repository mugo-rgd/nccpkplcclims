<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentManufacturer extends Model
{
    protected $table = 'equipment_manufacturers';

    protected $fillable = [
        'name',
        'code',
        'website',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Equipment supplied or manufactured by this manufacturer.
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(
            Equipment::class,
            'manufacturer_id'
        );
    }

    /**
     * Return only active manufacturers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Display the code and manufacturer name together.
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
