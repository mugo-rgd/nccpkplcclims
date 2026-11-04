<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'request_number',
        'equipment_type',
        'manufacturer',
        'model',
        'serial_number',
        'ratings',
        'service_type',
        'calibration_location', // NEW
        'method',
        'description',
        'status',
        'requires_calibration_validity',
        'validity_period_days',
        'request_date',
        'customer_approved_at',
        'paid_at',
        'notes',
        // NEW FIELDS
        'site_address',
        'site_contact_person',
        'site_contact_phone',
        'site_visit_date',
        'site_instructions',
        'site_access_requirements',
        'received_at_lab',
        'received_by',
        'equipment_condition_notes',
        'is_equipment_damaged',
        'collected_at',
        'collected_by',
        'collection_notes',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'customer_approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'site_visit_date' => 'datetime',
        'received_at_lab' => 'datetime',
        'collected_at' => 'datetime',
        'requires_calibration_validity' => 'boolean',
        'is_equipment_damaged' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'draft',
        'calibration_location' => 'laboratory',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function jobCard(): HasOne
    {
        return $this->hasOne(JobCard::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function deliveryRecords(): HasMany
    {
        return $this->hasMany(DeliveryRecord::class);
    }

    // NEW: Location specific methods
    public function isLaboratoryCalibration(): bool
    {
        return $this->calibration_location === 'laboratory';
    }

    public function isOnSiteCalibration(): bool
    {
        return $this->calibration_location === 'on_site';
    }

    public function hasEquipmentBeenReceived(): bool
    {
        return !is_null($this->received_at_lab);
    }

    public function hasEquipmentBeenCollected(): bool
    {
        return !is_null($this->collected_at);
    }

    public function hasSiteVisitScheduled(): bool
    {
        return !is_null($this->site_visit_date);
    }

    public function getLocationLabelAttribute(): string
    {
        return $this->isLaboratoryCalibration() ? 'Laboratory' : 'On-Site';
    }

    public function getLocationColorAttribute(): string
    {
        return $this->isLaboratoryCalibration() ? 'primary' : 'success';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'pending_quote' => 'Pending Quote',
            'quoted' => 'Quoted',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending_quote' => 'warning',
            'quoted' => 'info',
            'approved' => 'primary',
            'paid' => 'success',
            'processing' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function generateRequestNumber(): string
    {
        $sequence = Sequence::where('name', 'customer_request')->first();
        $next = $sequence->last_number + 1;
        $sequence->update(['last_number' => $next]);

        return $sequence->prefix . str_pad($next, $sequence->padding, '0', STR_PAD_LEFT);
    }
}
