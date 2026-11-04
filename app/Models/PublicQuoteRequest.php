<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class PublicQuoteRequest extends Model
{
   protected $fillable = [
    'reference_number',
    'name',
    'email',
    'phone',
    'company',
    'equipment_type_id',
    'quantity',
    'requirements',
    'status',
    'calibration_id',
    'reviewed_by',
    'reviewed_at',
    'converted_at',
    'internal_notes',
];

    protected $casts = [
        'quantity' => 'integer',
        'reviewed_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(
            Calibration::class
        );
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

   public function equipmentType(): BelongsTo
{
    return $this->belongsTo(
        KplcEquipmentType::class,
        'equipment_type_id'
    );
}
public function items()
{
    return $this->hasMany(PublicQuoteRequestItem::class);
}
}
