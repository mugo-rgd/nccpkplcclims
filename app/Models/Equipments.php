<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipments extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'customer_id',
        'serial_number',
        'manufacturer',
        'model',
        'accuracy_class',
        'status',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(Calibration::class);
    }
}
