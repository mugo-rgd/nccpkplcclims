<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalibrationItem extends Model
{
    protected $table = 'calibration_items';

    protected $fillable = [
        'calibration_id',
        'quote_request_item_id',
        'equipment_type_id',
        'equipment_name',
        'serial_number',
        'manufacturer',
        'model',
        'accuracy_class',
        'ratings',
        'equipment_condition',
        'service_types',
        'status',
        'calibration_location',
        'position',
        'notes',
        'test_results',
        'completed_at',
        'verification_of_item',
    'review_of_service',
    'validity_period_required',
    'validity_period',
    'validity_period_date',
    'laboratory_equipment_id',
    'test_parameters',
'temperature_start',
'temperature_end',
'humidity_start',
'humidity_end',
'supply_voltage',
'supply_frequency',
'test_parameters_recorded_by',
'test_parameters_recorded_at',
'review_status',
'testing_completed_at',
'testing_completed_by',
'reviewed_at',
'reviewed_by',
'approved_at',
'approved_by',
'review_comments',
'approval_comments',
'test_report_path',
'test_report_original_name',
'test_report_uploaded_by',
'test_report_uploaded_at',
    ];

    protected $casts = [
        'service_types' => 'array',
        'test_results'  => 'array',
        'completed_at'  => 'datetime',
        'test_parameters' => 'array',
'temperature_start' => 'decimal:2',
'temperature_end' => 'decimal:2',
'humidity_start' => 'decimal:2',
'humidity_end' => 'decimal:2',
'supply_voltage' => 'decimal:2',
'supply_frequency' => 'decimal:2',
'test_parameters_recorded_at' => 'datetime',
'testing_completed_at' => 'datetime',
'reviewed_at' => 'datetime',
'approved_at' => 'datetime',
'test_report_uploaded_at' => 'datetime',

    ];

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(Calibration::class);
    }

public function testResults()
{
    return $this->hasMany(
        TestResult::class,
        'calibration_item_id'
    );
}

public function testParametersRecordedBy()
{
    return $this->belongsTo(
        User::class,
        'test_parameters_recorded_by'
    );
}

public function laboratoryEquipment(): BelongsTo
{
    return $this->belongsTo(
        Equipment::class,
        'laboratory_equipment_id'
    );
}

public function testReportUploadedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'test_report_uploaded_by'
    );
}

public function testingCompletedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'testing_completed_by'
    );
}

public function reviewedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'reviewed_by'
    );
}

public function approvedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'approved_by'
    );
}

public function hasTestReport(): bool
{
    return filled($this->test_report_path)
        && \Illuminate\Support\Facades\Storage::disk('local')
            ->exists($this->test_report_path);
}
public function hasEnvironmentalConditions(): bool
{
    return !is_null($this->temperature_start)
        && !is_null($this->temperature_end)
        && !is_null($this->humidity_start)
        && !is_null($this->humidity_end)
        && !is_null($this->supply_voltage)
        && !is_null($this->supply_frequency)
        && !is_null($this->test_parameters_recorded_at);
}

public function getEnvironmentalConditionsSummaryAttribute(): string
{
    if (!$this->hasEnvironmentalConditions()) {
        return 'Not recorded';
    }

    return sprintf(
        'Temperature: %s–%s °C | Humidity: %s–%s %% | Supply: %s V @ %s Hz',
        $this->temperature_start,
        $this->temperature_end,
        $this->humidity_start,
        $this->humidity_end,
        $this->supply_voltage,
        $this->supply_frequency
    );
}
    public function quoteRequestItem(): BelongsTo
    {
        return $this->belongsTo(PublicQuoteRequestItem::class, 'quote_request_item_id');
    }

    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(KplcEquipmentType::class, 'equipment_type_id');
    }

    // app/Models/CalibrationItem.php

public function referenceInstruments()
{
    return $this->belongsToMany(
        \App\Models\Equipment::class,
        'calibration_item_reference_instruments',
        'calibration_item_id',
        'equipment_id'
    )
    ->withPivot([
        'certificate_number',
        'calibration_date',
        'calibration_due_date',
        'accuracy_class',
        'calibration_laboratory',
    ])
    ->withTimestamps();
}
}
