<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class JobCard extends Model
{
    protected $table = 'job_cards';

    protected $fillable = [
        'customer_id',
        'customer_request_id',
        'calibration_id',
        'job_card_number',
        'job_number', // Keep for backward compatibility
        'status',
        'description',
        'work_done',
        'remarks',
        'allocated_to',
        'allocated_by',
        'checked_by',
        'collected_by',
        'assigned_at',
        'started_at',
        'completed_at',
        'verified_at',
        'collected_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerRequest(): BelongsTo
    {
        return $this->belongsTo(CustomerRequest::class);
    }

    public function calibration(): BelongsTo
    {
        return $this->belongsTo(Calibration::class);
    }

    public function allocatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'allocated' => 'primary',
            'in_progress' => 'info',
            'completed' => 'success',
            'verified' => 'success',
            'collected' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    // ============================================
    // STATUS HELPER METHODS - ADD THESE
    // ============================================

    /**
     * Mark job card as allocated (assigned to technician)
     */
    public function markAsAllocated($technicianId)
    {
        $this->update([
            'status' => 'allocated',
            'allocated_to' => $technicianId,
            'allocated_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark job card as assigned (alias for markAsAllocated)
     */
    public function markAsAssigned($technicianId)
    {
        return $this->markAsAllocated($technicianId);
    }

    /**
     * Mark job card as in progress
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark job card as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark job card as verified
     */
    public function markAsVerified($userId)
    {
        $this->update([
            'status' => 'verified',
            'checked_by' => $userId,
            'verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark job card as collected
     */
    public function markAsCollected($userId)
    {
        $this->update([
            'status' => 'collected',
            'collected_by' => $userId,
            'collected_at' => now(),
        ]);

        return $this;
    }

    /**
     * Check if job card is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if job card is allocated
     */
    public function isAllocated(): bool
    {
        return $this->status === 'allocated';
    }

    /**
     * Check if job card is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if job card is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job card is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if job card is collected
     */
    public function isCollected(): bool
    {
        return $this->status === 'collected';
    }

    // ============================================
    // SCOPES - ADD THESE
    // ============================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAllocated($query)
    {
        return $query->where('status', 'allocated');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeCollected($query)
    {
        return $query->where('status', 'collected');
    }

    // ============================================
    // JOB NUMBER GENERATION - EXISTING METHODS
    // ============================================

    /**
     * Generate a unique job card number
     */
    public static function generateJobCardNumber(): string
    {
        $yearMonth = date('Ym');
        $prefix = 'KP1/13B.1//3/6/JC/' . date('Y') . date('m');

        // Use DB transaction to prevent race conditions
        return DB::transaction(function () use ($yearMonth, $prefix) {
            // Get or create sequence for this month
            $sequence = DB::table('job_card_sequences')
                ->where('year_month', $yearMonth)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequenceId = DB::table('job_card_sequences')->insertGetId([
                    'year_month' => $yearMonth,
                    'last_number' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $lastNumber = 0;
            } else {
                $lastNumber = $sequence->last_number;
            }

            // Increment
            $nextNumber = $lastNumber + 1;

            // Update sequence
            DB::table('job_card_sequences')
                ->where('year_month', $yearMonth)
                ->update(['last_number' => $nextNumber, 'updated_at' => now()]);

            return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Alternative: Use database transaction with lock
     */
    public static function generateUniqueJobCardNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $month = date('m');
            $prefix = 'KP1/13B.1//3/6/JC/' . $year . $month;

            // Lock the table for reading
            DB::statement('LOCK TABLES job_cards WRITE');

            $latest = self::where('job_number', 'LIKE', $prefix . '%')
                ->orWhere('job_card_number', 'LIKE', $prefix . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latest) {
                $latestNumber = (int) substr($latest->job_number ?? $latest->job_card_number, -4);
                $count = $latestNumber + 1;
            } else {
                $count = 1;
            }

            DB::statement('UNLOCK TABLES');

            return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
        });
    }

    // ============================================
    // ACCESSORS - ADD THESE
    // ============================================

    public function getFullJobNumberAttribute(): string
    {
        return $this->job_card_number ?? $this->job_number ?? 'JOB-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getJobNumberAttribute($value)
    {
        return $value ?? $this->job_card_number ?? 'JOB-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

}
