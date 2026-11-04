<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'equipment_number',
        'name',
        'description',
        'category_id',
        'type_id',
        'manufacturer_id',
        'model',
        'serial_number',
        'part_number',
        'accuracy_class',
        'ratings',
        'calibration_laboratory',
        'last_calibration_date',
        'next_calibration_date',
        'calibration_interval_days',
        'status',
        'condition',
        'location',
        'assigned_to',
        'remarks',
        'training_required',
        'procurement_notes',
        'purchase_date',
        'purchase_price',
        'supplier',
        'warranty_until',
        'technical_specifications',
        'accessories',
        'documents',
        'created_by',
        'updated_by',
        'reference_standard_cert_no',
        'reference_standard_cal_date',
        'reference_standard_due_date',
        'reference_standard_accuracy',
        'traceability_certificate_no',
         'last_calibration_reminder_at',
    'calibration_reminder_count',
    'calibration_reminder_enabled',
    'notes',

    ];

    protected $casts = [
        'last_calibration_date' => 'date',
        'next_calibration_date' => 'date',
        'reference_standard_cal_date' => 'date',
        'reference_standard_due_date' => 'date',
        'purchase_date' => 'date',
        'warranty_until' => 'date',
        'purchase_price' => 'decimal:2',
        'technical_specifications' => 'array',
        'accessories' => 'array',
        'documents' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_calibration_reminder_at' => 'datetime',
        'calibration_reminder_enabled' => 'boolean',
        'calibration_reminder_count' => 'integer',
    ];

    // Constants for statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_IN_USE = 'in_use';
    const STATUS_UNDER_REPAIR = 'under_repair';
    const STATUS_CALIBRATION_DUE = 'calibration_due';
    const STATUS_RETIRED = 'retired';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_IN_USE,
        self::STATUS_UNDER_REPAIR,
        self::STATUS_CALIBRATION_DUE,
        self::STATUS_RETIRED,
    ];

    const STATUS_COLORS = [
        self::STATUS_ACTIVE => 'success',
        self::STATUS_IN_USE => 'info',
        self::STATUS_UNDER_REPAIR => 'warning',
        self::STATUS_CALIBRATION_DUE => 'danger',
        self::STATUS_RETIRED => 'secondary',
    ];

    // Constants for conditions
    const CONDITION_EXCELLENT = 'excellent';
    const CONDITION_GOOD = 'good';
    const CONDITION_FAIR = 'fair';
    const CONDITION_POOR = 'poor';
    const CONDITION_NEEDS_REPAIR = 'needs_repair';

    const CONDITIONS = [
        self::CONDITION_EXCELLENT,
        self::CONDITION_GOOD,
        self::CONDITION_FAIR,
        self::CONDITION_POOR,
        self::CONDITION_NEEDS_REPAIR,
    ];

    const CONDITION_COLORS = [
        self::CONDITION_EXCELLENT => 'success',
        self::CONDITION_GOOD => 'info',
        self::CONDITION_FAIR => 'warning',
        self::CONDITION_POOR => 'danger',
        self::CONDITION_NEEDS_REPAIR => 'danger',
    ];

      public function type(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

        public function calibrationHistory(): HasMany
    {
        return $this->hasMany(EquipmentCalibrationHistory::class)->orderBy('calibration_date', 'desc');
    }

    public function latestCalibration(): HasOne
    {
        return $this->hasOne(EquipmentCalibrationHistory::class)->latest('calibration_date');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInUse($query)
    {
        return $query->where('status', self::STATUS_IN_USE);
    }

    public function scopeCalibrationDue($query, $days = 30)
    {
        return $query->where('next_calibration_date', '<=', now()->addDays($days))
                    ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_IN_USE]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_calibration_date', '<', now())
                    ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_IN_USE]);
    }

    public function scopeByLaboratory($query, $lab)
    {
        return $query->where('calibration_laboratory', $lab);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('type_id', $typeId);
    }

    public function scopeByManufacturer($query, $manufacturerId)
    {
        return $query->where('manufacturer_id', $manufacturerId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('equipment_number', 'LIKE', "%{$search}%")
              ->orWhere('serial_number', 'LIKE', "%{$search}%")
              ->orWhere('model', 'LIKE', "%{$search}%")
              ->orWhere('part_number', 'LIKE', "%{$search}%");
        });
    }

    // Accessors
    public function getIsCalibrationDueAttribute(): bool
    {
        if (!$this->next_calibration_date) {
            return false;
        }
        return $this->next_calibration_date <= now()->addDays(30);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (!$this->next_calibration_date) {
            return false;
        }
        return $this->next_calibration_date < now();
    }

    public function getDaysUntilCalibrationAttribute(): ?int
    {
        if (!$this->next_calibration_date) {
            return null;
        }
        return now()->diffInDays($this->next_calibration_date, false);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getConditionColorAttribute(): string
    {
        return self::CONDITION_COLORS[$this->condition] ?? 'secondary';
    }

    public function getFullNameAttribute(): string
    {
        $parts = [$this->name];
        if ($this->model) {
            $parts[] = '(' . $this->model . ')';
        }
        if ($this->serial_number) {
            $parts[] = '-' . $this->serial_number;
        }
        return implode(' ', $parts);
    }

    public function getFormattedPurchasePriceAttribute(): string
    {
        if (!$this->purchase_price) {
            return 'N/A';
        }
        return '$' . number_format($this->purchase_price, 2);
    }

    public function getReferenceStandardStatusAttribute(): string
    {
        if (!$this->reference_standard_due_date) {
            return 'not_available';
        }
        if ($this->reference_standard_due_date < now()) {
            return 'expired';
        }
        if ($this->reference_standard_due_date <= now()->addDays(30)) {
            return 'expiring_soon';
        }
        return 'valid';
    }

    public function getReferenceStandardStatusColorAttribute(): string
    {
        return match($this->reference_standard_status) {
            'valid' => 'success',
            'expiring_soon' => 'warning',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    // Mutators
    public function setPurchasePriceAttribute($value)
    {
        $this->attributes['purchase_price'] = $value ? floatval($value) : null;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipment) {
            if (empty($equipment->equipment_number)) {
                $equipment->equipment_number = self::generateEquipmentNumber();
            }
            if (Auth::check()) {
                $equipment->created_by = Auth::id();
            }
        });

        static::updating(function ($equipment) {
            if (Auth::check()) {
                $equipment->updated_by = Auth::id();
            }
        });

        // Auto-update status based on calibration dates
        static::saving(function ($equipment) {
            if ($equipment->next_calibration_date && $equipment->next_calibration_date < now()) {
                if (in_array($equipment->status, [self::STATUS_ACTIVE, self::STATUS_IN_USE])) {
                    $equipment->status = self::STATUS_CALIBRATION_DUE;
                }
            }
        });
    }

    // Helper Methods
    public static function generateEquipmentNumber(): string
    {
        $prefix = 'EQ-';
        $year = date('Y');
        $lastEquipment = self::where('equipment_number', 'LIKE', $prefix . $year . '%')
            ->orderBy('equipment_number', 'desc')
            ->first();

        if ($lastEquipment) {
            $lastNumber = (int) substr($lastEquipment->equipment_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $year . '-' . $newNumber;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_IN_USE;
    }

    public function canBeUsed(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        if ($this->is_overdue) {
            return false;
        }
        return true;
    }

    public function updateCalibrationDates(?string $lastDate, ?string $nextDate): void
    {
        if ($lastDate) {
            $this->last_calibration_date = $lastDate;
        }
        if ($nextDate) {
            $this->next_calibration_date = $nextDate;
        }
        if ($this->next_calibration_date && $this->next_calibration_date > now()) {
            $this->status = self::STATUS_ACTIVE;
        }
        $this->save();
    }

    public function recordCalibration(array $data): EquipmentCalibrationHistory
    {
        return $this->calibrationHistory()->create($data);
    }
    public function manufacturer(): BelongsTo
{
    return $this->belongsTo(
        EquipmentManufacturer::class,
        'manufacturer_id'
    );
}

 public function category(): BelongsTo
    {
        return $this->belongsTo(
            EquipmentCategory::class,
            'category_id'
        );
    }


    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(
            KplcEquipmentType::class,
            'type_id'
        );
    }


/**
 * Controlled equipment manufacturer.
 *
 * The relationship is named manufacturerRecord because the
 * equipment table may still contain a legacy manufacturer column.
 */
public function manufacturerRecord(): BelongsTo
{
    return $this->belongsTo(
        EquipmentManufacturer::class,
        'manufacturer_id'
    );
}

/**
 * Equipment type and published pricing category.
 */
public function pricingType(): BelongsTo
{
    return $this->belongsTo(
        KplcEquipmentType::class,
        'type_id'
    );
}


/**
 * Determine whether all required configuration values exist.
 */
public function getIsConfigurationCompleteAttribute(): bool
{
    return !empty($this->category_id)
        && !empty($this->manufacturer_id)
        && !empty($this->type_id);
}

/**
 * Return the names of missing configuration values.
 */
public function getMissingConfigurationAttribute(): array
{
    $missing = [];

    if (empty($this->category_id)) {
        $missing[] = 'Category';
    }

    if (empty($this->manufacturer_id)) {
        $missing[] = 'Manufacturer';
    }

    if (empty($this->type_id)) {
        $missing[] = 'Pricing Type';
    }

    return $missing;
}




public function scopeConfigurationComplete(
    Builder $query
): Builder {
    return $query
        ->whereNotNull('category_id')
        ->whereNotNull('manufacturer_id')
        ->whereNotNull('type_id');
}

public function scopeConfigurationIncomplete(
    Builder $query
): Builder {
    return $query->where(
        function (Builder $subQuery) {
            $subQuery
                ->whereNull('category_id')
                ->orWhereNull('manufacturer_id')
                ->orWhereNull('type_id');
        }
    );
}

public function categoryRecord(): BelongsTo
{
    return $this->belongsTo(
        EquipmentCategory::class,
        'category_id'
    );
}

// app/Models/Equipment.php

public function calibrationItemReferences()
{
    return $this->belongsToMany(
        \App\Models\CalibrationItem::class,
        'calibration_item_reference_instruments',
        'equipment_id',
        'calibration_item_id'
    )->withPivot([
        'certificate_number',
        'calibration_date',
        'calibration_due_date',
        'accuracy_class',
        'calibration_laboratory',
    ])->withTimestamps();
}

}
