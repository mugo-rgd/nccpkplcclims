<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCalibrationHistory extends Model
{
    // Remove SoftDeletes
    // use SoftDeletes;

    protected $table = 'equipment_calibration_history';

    protected $fillable = [
        'equipment_id',
        'calibration_date',
        'next_calibration_date',
        'calibration_laboratory',
        'certificate_number',
        'calibrated_by',
        'verified_by',
        'results',
        'notes',
        'status',
    ];

    protected $casts = [
        'calibration_date' => 'date',
        'next_calibration_date' => 'date',
        'results' => 'array',
    ];

    /**
     * Get the equipment that owns this calibration history.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the user who performed the calibration.
     */
    public function calibratedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calibrated_by');
    }

    /**
     * Get the user who verified the calibration.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope a query to only include successful calibrations.
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    /**
     * Scope a query to only include failed calibrations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
