<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KplcEquipmentType extends Model
{
    protected $table = 'kplc_equipment_types';

    protected $fillable = [
        'name',
        'rate',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
    ];

    /**
     * Calibrations belonging to this equipment type.
     */
    public function calibrations(): HasMany
    {
        return $this->hasMany(
            Calibration::class,
            'equipment_type_id'
        );
    }

    /**
     * Formatted laboratory rate.
     */
    public function getFormattedRateAttribute(): string
    {
        return 'KES ' . number_format(
            (float) $this->rate,
            2
        );
    }
}
