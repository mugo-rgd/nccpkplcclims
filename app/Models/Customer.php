<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_id',
        'credit_limit',
        'payment_terms',
        'registration_date',
        'status',
        'is_active',
        'notes',
        'is_tax_exempt',
'vat_rate',
'tax_exemption_certificate',
'tax_exemption_expiry',
'vat_exemption_status',
'customer_vat_rate',
'vat_exemption_certificate_number',
'vat_exemption_expiry_date',
'vat_verified_by',
'vat_verified_at',
'vat_verification_notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'registration_date' => 'date',
        'is_active' => 'boolean',
        'notes' => 'array',
        'is_tax_exempt' => 'boolean',
'vat_rate' => 'decimal:2',
'tax_exemption_expiry' => 'date',
'customer_vat_rate' => 'decimal:2',
'vat_exemption_expiry_date' => 'date',
'vat_verified_at' => 'datetime',
    ];

    /**
     * Get the user associated with this customer
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all calibrations for this customer
     */
    public function calibrations()
    {
        return $this->hasMany(Calibration::class);
    }

    /**
     * Get all certificates for this customer
     * Note: This assumes there's a certificates table with customer_id
     * If not, you may need to adjust this
     */
    public function certificates()
    {
        // If certificates table has customer_id column
        return $this->hasMany(Certificate::class);
        // If certificates are related through calibrations:
        // return $this->hasManyThrough(Certificate::class, Calibration::class);
    }

    /**
     * Get equipment through calibrations
     */
    public function getEquipmentAttribute()
    {
        return $this->calibrations()
            ->with('equipment')
            ->get()
            ->pluck('equipment')
            ->filter()
            ->unique('id');
    }

    /**
     * Get unique equipment names used by this customer
     */
    public function getUniqueEquipmentAttribute()
    {
        return $this->calibrations()
            ->whereNotNull('equipment_name')
            ->distinct('equipment_name')
            ->pluck('equipment_name');
    }

    /**
     * Get total count of equipment items
     */
    public function getTotalEquipmentCountAttribute()
    {
        return $this->calibrations()
            ->whereNotNull('equipment_name')
            ->distinct('equipment_name')
            ->count();
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope for pending customers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for suspended customers
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Check if customer is Kenya Power (Internal)
     */
    public function isKplc(): bool
    {
        // Check company name
        $kplcKeywords = ['KPLC', 'Kenya Power', 'Kenya Power & Lighting', 'K.P.L.C'];
        foreach ($kplcKeywords as $keyword) {
            if (stripos($this->company_name, $keyword) !== false) {
                return true;
            }
        }

        // Check notes for customer_type
        if ($this->notes && isset($this->notes['customer_type'])) {
            return $this->notes['customer_type'] === 'kplc';
        }

        // Check if email domain is kplc.co.ke
        if ($this->email && str_ends_with(strtolower($this->email), '@kplc.co.ke')) {
            return true;
        }

        return false;
    }

    /**
     * Check if customer is external
     */
    public function isExternal(): bool
    {
        return !$this->isKplc();
    }

    /**
     * Get customer type label
     */
    public function getCustomerTypeAttribute(): string
    {
        return $this->isKplc() ? 'Internal (KPLC)' : 'External';
    }

    /**
     * Get customer type badge color
     */
    public function getCustomerTypeBadgeAttribute(): string
    {
        return $this->isKplc() ? 'primary' : 'secondary';
    }

    /**
     * Get full address as a string
     */
    public function getFullAddressAttribute(): string
    {
        return $this->address ?? 'N/A';
    }

    /**
     * Get customer statistics
     */
    public function getStatistics()
    {
        $calibrations = $this->calibrations;
        $certificates = $this->certificates;

        return [
            'total_calibrations' => $calibrations->count(),
            'pending_calibrations' => $calibrations->where('status', 'pending')->count(),
            'in_progress_calibrations' => $calibrations->whereIn('status', ['assigned', 'in_progress', 'testing'])->count(),
            'completed_calibrations' => $calibrations->whereIn('status', ['completed', 'certified'])->count(),
            'rejected_calibrations' => $calibrations->where('status', 'rejected')->count(),
            'total_certificates' => $certificates->count(),
            'approved_certificates' => $certificates->where('status', 'approved')->count(),
            'pending_certificates' => $certificates->where('status', 'pending')->count(),
            'total_equipment' => $this->calibrations()
                ->whereNotNull('equipment_name')
                ->distinct('equipment_name')
                ->count(),
            'total_spent' => $this->calibrations()->sum('amount_paid'),
            'total_due' => $this->calibrations()->sum('amount_due'),
        ];
    }

    /**
     * Get recent calibrations
     */
    public function getRecentCalibrations($limit = 5)
    {
        return $this->calibrations()
            ->with(['equipment', 'performedBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('company_name', 'LIKE', "%{$search}%")
              ->orWhere('contact_person', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('tax_id', 'LIKE', "%{$search}%")
              ->orWhere('address', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Filter by customer type (internal/external)
     */
    public function scopeFilterByType($query, $type)
    {
        if ($type === 'internal') {
            return $query->where('company_name', 'LIKE', '%KPLC%')
                        ->orWhere('company_name', 'LIKE', '%Kenya Power%');
        } elseif ($type === 'external') {
            return $query->where('company_name', 'NOT LIKE', '%KPLC%')
                        ->where('company_name', 'NOT LIKE', '%Kenya Power%');
        }
        return $query;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->registration_date)) {
                $customer->registration_date = now();
            }
            if (empty($customer->payment_terms)) {
                $customer->payment_terms = '30_days';
            }
            if (empty($customer->status)) {
                $customer->status = 'pending';
            }
            if (empty($customer->is_active)) {
                $customer->is_active = true;
            }
        });
    }

    /**
     * Check if customer is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'active' && $this->is_active;
    }

    /**
     * Check if customer is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if customer is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || !$this->is_active;
    }

    /**
     * Get the display name for the customer
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name . ' (' . $this->contact_person . ')';
    }

    /**
     * Get the short name (first word of company name)
     */
    public function getShortNameAttribute(): string
    {
        $words = explode(' ', $this->company_name);
        return $words[0] ?? $this->company_name;
    }

    /**
     * Get customer initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->company_name);
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        return $initials ?: 'CUST';
    }

    /**
     * Get the total amount paid by this customer
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->calibrations()->sum('amount_paid');
    }

    /**
     * Get the total amount due by this customer
     */
    public function getTotalDueAttribute(): float
    {
        return (float) $this->calibrations()->sum('amount_due');
    }

    /**
     * Get the balance (due - paid)
     */
    public function getBalanceAttribute(): float
    {
        return $this->total_due - $this->total_paid;
    }

    /**
     * Check if customer has pending payments
     */
    public function hasPendingPayments(): bool
    {
        return $this->balance > 0;
    }

    public function taxDocuments()
{
    return $this->hasMany(
        CustomerTaxDocument::class,
        'customer_id'
    );
}

}
