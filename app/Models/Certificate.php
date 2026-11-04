<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'calibration_id',
        'customer_id',
        'certificate_number',
           'calibration_item_id',
            'reference_number',
        'equipment_name',
        'equipment_id',
        'serial_number',
        'manufacturer',
        'model',
        'equipment_type',
        'accuracy_class',
        'ratings',
        'service_name',
        'service_method',
        'service_notes',
        'voltage',
        'current',
        'temperature',
        'humidity',
        'equipment_constant',
        'equipment_class',
        'wiring',
        'test_results',
        'test_result',
        'traceability_information',
        'statement_conformity',
        'comments',
        'status',
        'generated_by',
        'checked_by',
        'approved_by',
        'issued_by',
        'sent_by',
        'generated_at',
        'checked_at',
        'approved_at',
        'issued_date',
        'sent_at',
        'expiry_date',
        'is_signed',
        'signed_by',
        'signed_at',
        'pdf_path',
        'test_report_attachment_path',
        'test_report_attachment_filename',
        'test_report_original_name',
        'machine_used',
        'machine_serial',
        'test_engineer',
        'test_date',
    ];

    protected $casts = [
        'test_results' => 'array',
        'voltage' => 'decimal:2',
        'current' => 'decimal:2',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'equipment_constant' => 'decimal:2',
        'generated_at' => 'datetime',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'issued_date' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'test_date' => 'datetime',
        'signed_at' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    // Relationships
    public function calibration(): BelongsTo
    {
        return $this->belongsTo(Calibration::class);
    }

    public function calibrationItem(): BelongsTo
{
    return $this->belongsTo(
        \App\Models\CalibrationItem::class,
        'calibration_item_id'
    );
}

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'generated' => 'Generated',
            'checked' => 'Checked',
            'approved' => 'Approved',
            'sent' => 'Sent to Customer',
            'rejected' => 'Rejected',
            'issued' => 'Issued',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'generated' => 'info',
            'checked' => 'primary',
            'approved' => 'success',
            'sent' => 'success',
            'rejected' => 'danger',
            'issued' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Get sanitized certificate number for filename
     */
    public function getSanitizedCertificateNumberAttribute(): string
    {
        return str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $this->certificate_number);
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'KP1/13B.1/5/3/' . $year . '/' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get test report attachment URL
     */
    public function getTestReportAttachmentUrlAttribute(): ?string
    {
        if ($this->test_report_attachment_path) {
            return asset('storage/' . $this->test_report_attachment_path);
        }
        return null;
    }

    public function getSignatureUrl($user, $field = 'signature_path')
{
    if (!$user) {
        return null;
    }

    if ($user->use_auto_signature && $user->signature_path) {
        // Check if it's a base64 image
        if (Str::startsWith($user->signature_path, 'data:image')) {
            return $user->signature_path;
        }
        return Storage::url($user->signature_path);
    }

    return null;
}

public function checker()
{
    return $this->belongsTo(
        User::class,
        'checked_by'
    );
}

public function calibrations()
{
    return $this->hasMany(
        \App\Models\Calibration::class,
        'performed_by'
    );
}

public function generatedCertificates()
{
    return $this->hasMany(
        \App\Models\Certificate::class,
        'generated_by'
    );
}

public function checkedCertificates()
{
    return $this->hasMany(
        \App\Models\Certificate::class,
        'checked_by'
    );
}

public function approvedCertificates()
{
    return $this->hasMany(
        \App\Models\Certificate::class,
        'approved_by'
    );
}

public function issuedCertificates()
{
    return $this->hasMany(
        \App\Models\Certificate::class,
        'issued_by'
    );
}

public function sentCertificates()
{
    return $this->hasMany(
        \App\Models\Certificate::class,
        'sent_by'
    );
}
}
