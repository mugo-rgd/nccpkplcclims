<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReassignmentHistory extends Model
{
    protected $fillable = [
        'calibration_id',
        'from_technician_id',
        'to_technician_id',
        'reassigned_by',
        'reason',
        'reassigned_at',
    ];

    protected $casts = [
        'reassigned_at' => 'datetime',
    ];

    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }

    public function fromTechnician()
    {
        return $this->belongsTo(User::class, 'from_technician_id');
    }

    public function toTechnician()
    {
        return $this->belongsTo(User::class, 'to_technician_id');
    }

    public function reassignedBy()
    {
        return $this->belongsTo(User::class, 'reassigned_by');
    }
}
