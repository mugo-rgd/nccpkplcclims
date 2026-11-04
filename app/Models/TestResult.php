<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing fields
        'calibration_id',
        'parameter',
        'nominal_value',
        'measured_value',
        'calibration_item_id',
        'tolerance_min',
        'tolerance_max',
        'unit',
        'is_within_tolerance',
        'error_percentage',
        'notes',

        // New certificate test fields
        'test_name',
        'test_type',
        'applied_value',
        'power_factor',
        'voltage',
        'frequency',
        'trial_data',
        'uncertainty',
        'error_value',        // Add this
    'tolerance',          // Add this
        'start_reading',
        'stop_reading',
        'energy_difference',
        'dial_error',
        'test_order',
        'result'
    ];

    protected $casts = [
        'nominal_value' => 'decimal:6',
        'measured_value' => 'decimal:6',
        'tolerance_min' => 'decimal:6',
        'tolerance_max' => 'decimal:6',
        'error_percentage' => 'decimal:4',
        'uncertainty' => 'decimal:6',
        'start_reading' => 'decimal:2',
        'stop_reading' => 'decimal:2',
        'energy_difference' => 'decimal:2',
        'dial_error' => 'decimal:4',
        'is_within_tolerance' => 'boolean',
        'trial_data' => 'array'
    ];

    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }

    public function uncertaintyCalculation()
    {
        return $this->hasOne(UncertaintyCalculation::class);
    }

    // app/Models/TestResult.php
public function calibrationItem()
{
    return $this->belongsTo(CalibrationItem::class);
}
    // Helper methods for certificate testing

    public function getTrialCountAttribute()
    {
        return $this->trial_data ? count($this->trial_data) : 0;
    }

    public function getAverageErrorAttribute()
    {
        if (!$this->trial_data || empty($this->trial_data)) {
            return $this->error_percentage;
        }
        return collect($this->trial_data)->average();
    }

    public function getStandardDeviationAttribute()
    {
        if (!$this->trial_data || count($this->trial_data) < 2) {
            return null;
        }
        $avg = $this->average_error;
        $sum = collect($this->trial_data)->sum(function($val) use ($avg) {
            return pow($val - $avg, 2);
        });
        return sqrt($sum / (count($this->trial_data) - 1));
    }

    public function isPass()
    {
        return $this->result === 'PASS' || $this->is_within_tolerance;
    }

    // Get display label for test type
    public function getTestTypeLabelAttribute()
    {
        $labels = [
            'accuracy' => 'Accuracy Test',
            'register' => 'Register Test',
            'starting' => 'Starting Current Test',
            'creep' => 'Creep Test'
        ];
        return $labels[$this->test_type] ?? ucfirst($this->test_type);
    }

    // Get display label for power factor
    public function getPowerFactorLabelAttribute()
    {
        $labels = [
            '1.0PF' => '1.0 Power Factor',
            '0.5LAG' => '0.5 Lagging Power Factor',
            '0.5LEAD' => '0.5 Leading Power Factor'
        ];
        return $labels[$this->power_factor] ?? $this->power_factor;
    }


}
