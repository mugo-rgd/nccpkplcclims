<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Calibration extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;


    /*
    |--------------------------------------------------------------------------
    | STATUS CONSTANTS
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_PAID = 'paid';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_TESTING = 'testing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CERTIFIED = 'certified';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_DISPATCHED = 'dispatched';


    /*
    |--------------------------------------------------------------------------
    | EXTERNAL CUSTOMER STATUS FLOW
    |--------------------------------------------------------------------------
    */

    public const FLOW_EXTERNAL = [

        self::STATUS_PENDING => [
            'next' => self::STATUS_QUOTED,
            'label' => 'Pending',
        ],

        self::STATUS_QUOTED => [
            'next' => self::STATUS_PAID,
            'label' => 'Quoted',
        ],

        self::STATUS_PAID => [
            'next' => self::STATUS_ASSIGNED,
            'label' => 'Paid',
        ],

        self::STATUS_ASSIGNED => [
            'next' => self::STATUS_IN_PROGRESS,
            'label' => 'Assigned',
        ],

        self::STATUS_IN_PROGRESS => [
            'next' => self::STATUS_TESTING,
            'label' => 'In Progress',
        ],

        self::STATUS_TESTING => [
            'next' => self::STATUS_COMPLETED,
            'label' => 'Testing',
        ],

        self::STATUS_COMPLETED => [
            'next' => self::STATUS_CERTIFIED,
            'label' => 'Completed',
        ],

        self::STATUS_CERTIFIED => [
            'next' => null,
            'label' => 'Certified',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | INTERNAL KPLC STATUS FLOW
    |--------------------------------------------------------------------------
    */

    public const FLOW_INTERNAL = [

        self::STATUS_PENDING => [
            'next' => self::STATUS_ASSIGNED,
            'label' => 'Pending',
        ],

        self::STATUS_ASSIGNED => [
            'next' => self::STATUS_IN_PROGRESS,
            'label' => 'Assigned',
        ],

        self::STATUS_IN_PROGRESS => [
            'next' => self::STATUS_TESTING,
            'label' => 'In Progress',
        ],

        self::STATUS_TESTING => [
            'next' => self::STATUS_COMPLETED,
            'label' => 'Testing',
        ],

        self::STATUS_COMPLETED => [
            'next' => self::STATUS_CERTIFIED,
            'label' => 'Completed',
        ],

        self::STATUS_CERTIFIED => [
            'next' => null,
            'label' => 'Certified',
        ],

    ];


    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNABLE ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Customer / Batch
        |--------------------------------------------------------------------------
        */

        'batch_id',
        'batch_number',
        'customer_id',
'quote_request_id',

        /*
        |--------------------------------------------------------------------------
        | Customer Equipment Being Calibrated
        |--------------------------------------------------------------------------
        */

        'equipment_name',
        'serial_number',
        'manufacturer',
        'model',
        'accuracy_class',
        'ratings',
        'equipment_type',
        'equipment_type_id',
        'equipment_condition',
        'last_calibration_date',


        /*
        |--------------------------------------------------------------------------
        | MCL Laboratory Equipment / Assignment
        |--------------------------------------------------------------------------
        */

        'equipment_id',

        'created_by',
        'performed_by',

        'assigned_at',
        'started_at',

        'assignment_notes',


        /*
        |--------------------------------------------------------------------------
        | Calibration Details
        |--------------------------------------------------------------------------
        */

        'status',

        'is_external',
        'requires_payment',

        'scheduled_date',
        'completed_at',

        'calibration_location',
        'priority',

        'service_types',

        'notes',
        'rejection_reason',


        /*
        |--------------------------------------------------------------------------
        | Test Parameters / Environmental Conditions
        |--------------------------------------------------------------------------
        */

        'test_parameters',

        'temperature_start',
        'temperature_end',

        'humidity_start',
        'humidity_end',

        'supply_voltage',
        'supply_frequency',

        'test_parameters_recorded_by',
        'test_parameters_recorded_at',

        'test_results',


        /*
        |--------------------------------------------------------------------------
        | Customer Verification / Validity
        |--------------------------------------------------------------------------
        */

        'verification_of_item',
        'review_of_service',

        'validity_period_required',
        'validity_period',
        'validity_period_date',


        /*
        |--------------------------------------------------------------------------
        | Quotation / Payment
        |--------------------------------------------------------------------------
        */

        'quote_sent_at',
        'quote_accepted_at',
        'payment_received_at',

        'payment_status',

        'amount_due',
        'amount_paid',

        'payment_method',
        'payment_reference',
        'payment_date',


        /*
        |--------------------------------------------------------------------------
        | On-site Costs
        |--------------------------------------------------------------------------
        */

        'per_diem_cost',
        'transport_cost',
        'other_costs',

        'other_costs_description',

        'number_of_staff',
        'travel_days',
        'site_distance_km',


        /*
        |--------------------------------------------------------------------------
        | Quality Control
        |--------------------------------------------------------------------------
        */

        'quality_checked',
        'quality_checked_at',
        'quality_checked_by',

        'quality_approved',
        'quality_rejected',

        'quality_notes',
        'quality_checklist',


        /*
        |--------------------------------------------------------------------------
        | Technician Reassignment
        |--------------------------------------------------------------------------
        */

        'reassigned_at',
        'reassigned_by',
        'previous_technician_id',


        /*
        |--------------------------------------------------------------------------
        | Generated Documents
        |--------------------------------------------------------------------------
        */

        'delivery_number',
        'collection_number',

        'request_form_path',
        'delivery_note_path',
        'collection_note_path',
        'jobcard_form_path',

    ];


    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTE CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */

        'service_types' => 'array',
        'test_parameters' => 'array',
        'test_results' => 'array',
        'quality_checklist' => 'array',


        /*
        |--------------------------------------------------------------------------
        | Dates / Timestamps
        |--------------------------------------------------------------------------
        */

        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime',

        'last_calibration_date' => 'date',

        'assigned_at' => 'datetime',
        'started_at' => 'datetime',

        'quote_sent_at' => 'datetime',
        'quote_accepted_at' => 'datetime',
        'payment_received_at' => 'datetime',

        'payment_date' => 'datetime',

        'quality_checked_at' => 'datetime',

        'reassigned_at' => 'datetime',

        'test_parameters_recorded_at' => 'datetime',

        'validity_period_date' => 'date',


        /*
        |--------------------------------------------------------------------------
        | Environmental Conditions
        |--------------------------------------------------------------------------
        */

        'temperature_start' => 'decimal:2',
        'temperature_end' => 'decimal:2',

        'humidity_start' => 'decimal:2',
        'humidity_end' => 'decimal:2',

        'supply_voltage' => 'decimal:2',
        'supply_frequency' => 'decimal:2',


        /*
        |--------------------------------------------------------------------------
        | Financial / Numeric
        |--------------------------------------------------------------------------
        */

        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',

        'per_diem_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'other_costs' => 'decimal:2',

        'number_of_staff' => 'integer',
        'travel_days' => 'integer',
        'site_distance_km' => 'integer',


        /*
        |--------------------------------------------------------------------------
        | Boolean
        |--------------------------------------------------------------------------
        */

        'is_external' => 'boolean',
        'requires_payment' => 'boolean',

        'quality_checked' => 'boolean',
        'quality_approved' => 'boolean',
        'quality_rejected' => 'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */


    /**
     * Customer that owns this calibration.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }


    /**
     * MCL equipment used during calibration/testing.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(
            Equipment::class,
            'equipment_id'
        );
    }


    /**
     * Equipment type.
     */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(
            KplcEquipmentType::class,
            'equipment_type_id'
        );
    }


    /**
     * Technician assigned to perform calibration.
     */
    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }


    /**
     * Compatibility alias.
     *
     * Existing code may reference:
     *
     * $calibration->technician
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }


    /**
     * Technician who recorded environmental/test parameters.
     */
    public function testParametersRecordedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'test_parameters_recorded_by'
        );
    }


    /**
     * User who performed quality check.
     */
    public function qualityCheckedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'quality_checked_by'
        );
    }


    /**
     * Previous technician before reassignment.
     */
    public function previousTechnician(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'previous_technician_id'
        );
    }


    /**
     * User who reassigned calibration.
     */
    public function reassignedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reassigned_by'
        );
    }


    /**
     * Existing singular certificate relationship.
     *
     * Retained because current code uses:
     *
     * $calibration->certificate
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(
            Certificate::class,
            'calibration_id'
        );
    }


    /**
     * All certificates belonging to calibration.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(
            Certificate::class,
            'calibration_id'
        );
    }


    /**
     * Latest certificate.
     */
    public function latestCertificate(): HasOne
    {
        return $this
            ->hasOne(
                Certificate::class,
                'calibration_id'
            )
            ->latestOfMany();
    }


    /**
     * Test results.
     */
    public function testResults(): HasMany
    {
        return $this->hasMany(
            TestResult::class,
            'calibration_id'
        );
    }


    /**
     * Job card.
     */
    public function jobCard(): HasOne
    {
        return $this->hasOne(
            JobCard::class,
            'calibration_id'
        );
    }


    /**
     * Quotation.
     */
    public function quotation(): HasOne
    {
        return $this->hasOne(
            Quote::class,
            'calibration_id'
        );
    }


    /**
     * Technician reassignment history.
     */
    public function reassignmentHistories(): HasMany
    {
        return $this->hasMany(
            ReassignmentHistory::class,
            'calibration_id'
        );
    }


    /**
     * Compatibility alias retained because some code
     * may reference reassignmentHistory().
     */
    public function reassignmentHistory(): HasMany
    {
        return $this->reassignmentHistories();
    }


    /*
    |--------------------------------------------------------------------------
    | ENVIRONMENTAL / TEST PARAMETER HELPERS
    |--------------------------------------------------------------------------
    */


    /**
     * Determine whether all mandatory environmental conditions
     * have been recorded.
     */
    public function hasEnvironmentalConditions(): bool
    {
        return !is_null($this->temperature_start)
            && !is_null($this->temperature_end)
            && !is_null($this->humidity_start)
            && !is_null($this->humidity_end)
            && !is_null($this->supply_voltage)
            && !is_null($this->supply_frequency);
    }


    /**
     * Check whether the currently logged-in technician may
     * record environmental conditions.
     *
     * Conditions:
     * - calibration must be in testing
     * - logged-in user must be performed_by
     */
    public function canRecordEnvironmentalConditions(): bool
    {
        return $this->status === self::STATUS_TESTING
            && $this->isAssignedTechnician();
    }


    /**
     * Human-readable environmental conditions summary.
     *
     * Example:
     *
     * Temperature: 27.10-27.90 °C
     * Relative Humidity: 53.10-54.80 %
     * Supply: 230.00 V @ 50.00 Hz
     */
    public function getEnvironmentalConditionsSummaryAttribute(): string
    {
        if (!$this->hasEnvironmentalConditions()) {
            return 'Not recorded';
        }

        return sprintf(
            'Temperature: %s-%s °C | Relative Humidity: %s-%s %% | Supply: %s V @ %s Hz',
            $this->temperature_start,
            $this->temperature_end,
            $this->humidity_start,
            $this->humidity_end,
            $this->supply_voltage,
            $this->supply_frequency
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT HELPERS
    |--------------------------------------------------------------------------
    */


    /**
     * Determine whether calibration requires payment.
     */
    public function requiresPayment(): bool
    {
        /*
         * Explicit external calibration requiring payment
         * and having a charge.
         */
        if (
            $this->is_external
            && $this->requires_payment
            && (float) ($this->amount_due ?? 0) > 0
        ) {
            return true;
        }


        /*
         * External non-KPLC customer.
         */
        if (
            $this->customer
            && $this->customer->isExternal()
            && !$this->customer->isKplc()
        ) {
            return true;
        }


        return false;
    }


    /**
     * Check whether payment is completed.
     */
    public function isPaymentCompleted(): bool
    {
        /*
         * No payment required.
         */
        if (!$this->requires_payment) {
            return true;
        }


        /*
         * Explicitly marked paid.
         */
        if ($this->payment_status === 'paid') {
            return true;
        }


        /*
         * Zero amount means there is nothing outstanding.
         */
        if ((float) ($this->amount_due ?? 0) <= 0) {
            return true;
        }


        /*
         * Paid amount covers amount due.
         */
        return (float) ($this->amount_paid ?? 0)
            >= (float) ($this->amount_due ?? 0);
    }


    /**
     * Check whether payment is pending.
     */
    public function isPaymentPending(): bool
    {
        if (!$this->requiresPayment()) {
            return false;
        }


        return
            $this->payment_status === 'pending'

            || $this->status === self::STATUS_QUOTED

            || (
                (float) ($this->amount_paid ?? 0) > 0

                && (float) ($this->amount_paid ?? 0)
                    < (float) ($this->amount_due ?? 0)
            );
    }


    /**
     * Determine whether calibration can proceed.
     */
    public function canProceed(): bool
    {
        if ($this->requiresPayment()) {
            return $this->isPaymentCompleted();
        }


        return true;
    }


    /**
     * Payment status label.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        if (!$this->requiresPayment()) {
            return 'Not Required';
        }


        if ($this->isPaymentCompleted()) {
            return 'Paid ✓';
        }


        if ($this->isPaymentPending()) {

            if ((float) ($this->amount_paid ?? 0) > 0) {

                return 'Partial ('
                    . number_format(
                        (float) $this->amount_paid,
                        2
                    )
                    . '/'
                    . number_format(
                        (float) $this->amount_due,
                        2
                    )
                    . ')';
            }


            return 'Pending ⏳';
        }


        return 'Unpaid ❌';
    }


    /**
     * Payment status Bootstrap colour.
     */
    public function getPaymentStatusColorAttribute(): string
    {
        if (!$this->requiresPayment()) {
            return 'secondary';
        }


        if ($this->isPaymentCompleted()) {
            return 'success';
        }


        if ($this->isPaymentPending()) {

            return (float) ($this->amount_paid ?? 0) > 0
                ? 'warning'
                : 'danger';
        }


        return 'danger';
    }


    /**
     * Formatted amount due.
     */
    public function getFormattedAmountDueAttribute(): string
    {
        return 'KSh '
            . number_format(
                (float) ($this->amount_due ?? 0),
                2
            );
    }


    /**
     * Formatted amount paid.
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        return 'KSh '
            . number_format(
                (float) ($this->amount_paid ?? 0),
                2
            );
    }


    /**
     * Outstanding balance.
     */
    public function getBalanceDueAttribute(): float
    {
        return max(
            0,

            (float) ($this->amount_due ?? 0)
                -
            (float) ($this->amount_paid ?? 0)
        );
    }


    /**
     * Formatted outstanding balance.
     */
    public function getFormattedBalanceDueAttribute(): string
    {
        return 'KSh '
            . number_format(
                $this->balance_due,
                2
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CALIBRATION WORKFLOW CHECKS
    |--------------------------------------------------------------------------
    */


    /**
     * Check whether calibration can be assigned.
     */
    public function canBeAssigned(): bool
    {
        /*
         * External calibrations requiring payment
         * must be fully paid first.
         */
        if (
            $this->is_external
            && $this->requires_payment
        ) {
            return $this->isPaymentCompleted();
        }


        /*
         * Allow reassignment.
         */
        if (
            $this->status === self::STATUS_ASSIGNED
            && $this->performed_by
        ) {
            return true;
        }


        return in_array(
            $this->status,
            [
                self::STATUS_PENDING,
                self::STATUS_PAID,
                self::STATUS_QUOTED,
            ],
            true
        );
    }


    /**
     * Check whether calibration can be paid.
     */
    public function canBePaid(): bool
    {
        return
            $this->status === self::STATUS_QUOTED

            && $this->customer

            && $this->customer->isExternal()

            && $this->requiresPayment();
    }


    /**
     * Check whether calibration can enter testing workflow.
     */
    public function canBeTested(): bool
    {
        if (
            $this->requiresPayment()
            && !$this->isPaymentCompleted()
        ) {
            return false;
        }


        return in_array(
            $this->status,
            [
                self::STATUS_ASSIGNED,
                self::STATUS_IN_PROGRESS,
            ],
            true
        );
    }


    /**
     * Check whether calibration can be completed.
     *
     * Stage 2 will add controller validation requiring:
     *
     * - test parameters/environmental conditions
     * - test results
     */
    public function canBeCompleted(): bool
    {
        if (
            $this->requiresPayment()
            && !$this->isPaymentCompleted()
        ) {
            return false;
        }


        return
            $this->status === self::STATUS_TESTING;
    }


    /**
     * Check whether calibration can be quoted.
     */
    public function canBeQuoted(): bool
    {
        return
            $this->status === self::STATUS_PENDING

            && $this->customer

            && !$this->customer->isKplc();
    }


    /**
     * Check whether certificate generation is allowed
     * by calibration status.
     *
     * Environmental-condition enforcement will also be
     * applied in the certificate/controller workflow.
     */
    public function canGenerateCertificate(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_COMPLETED,
                self::STATUS_CERTIFIED,
            ],
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SERVICE TYPE HELPERS
    |--------------------------------------------------------------------------
    */


    public function getServiceTypesListAttribute()
    {
        if (
            empty($this->service_types)
            || !is_array($this->service_types)
        ) {
            return collect();
        }


        return KplcServiceType::whereIn(
            'id',
            $this->service_types
        )->get();
    }


    /*
    |--------------------------------------------------------------------------
    | EQUIPMENT TYPE HELPERS
    |--------------------------------------------------------------------------
    */


    public function getEquipmentTypeRateAttribute(): float
    {
        if (!$this->equipment_type_id) {
            return 0;
        }


        $type = KplcEquipmentType::find(
            $this->equipment_type_id
        );


        return $type
            ? (float) $type->rate
            : 0;
    }


    public function getEquipmentTypeNameAttribute(): string
    {
        if (!$this->equipment_type_id) {
            return 'N/A';
        }


        $type = KplcEquipmentType::find(
            $this->equipment_type_id
        );


        return $type
            ? $type->name
            : 'N/A';
    }


    /*
    |--------------------------------------------------------------------------
    | ON-SITE COST HELPERS
    |--------------------------------------------------------------------------
    */


    public function getTotalOnSiteCostAttribute(): float
    {
        return
            (float) ($this->per_diem_cost ?? 0)

            + (float) ($this->transport_cost ?? 0)

            + (float) ($this->other_costs ?? 0);
    }


    public function getIsOnSiteAttribute(): bool
    {
        return
            $this->calibration_location === 'on_site';
    }


    /*
    |--------------------------------------------------------------------------
    | AVAILABLE STATUS TRANSITIONS
    |--------------------------------------------------------------------------
    */


    public function getAvailableStatuses(): array
    {
        $statuses = [];


        switch ($this->status) {

            /*
             * Pending
             */
            case self::STATUS_PENDING:

                if (
                    $this->customer
                    && $this->customer->isKplc()
                ) {

                    if ($this->canBeAssigned()) {
                        $statuses[] =
                            self::STATUS_ASSIGNED;
                    }

                } else {

                    $statuses[] =
                        self::STATUS_QUOTED;
                }

                break;


            /*
             * Quoted
             */
            case self::STATUS_QUOTED:

                if ($this->canBePaid()) {

                    $statuses[] =
                        self::STATUS_PAID;
                }

                break;


            /*
             * Paid
             */
            case self::STATUS_PAID:

                if ($this->canBeAssigned()) {

                    $statuses[] =
                        self::STATUS_ASSIGNED;
                }

                break;


            /*
             * Assigned
             */
            case self::STATUS_ASSIGNED:

                if ($this->canBeTested()) {

                    $statuses[] =
                        self::STATUS_IN_PROGRESS;
                }

                break;


            /*
             * In Progress
             */
            case self::STATUS_IN_PROGRESS:

                if ($this->canBeTested()) {

                    $statuses[] =
                        self::STATUS_TESTING;
                }

                break;


            /*
             * Testing
             */
            case self::STATUS_TESTING:

                if ($this->canBeCompleted()) {

                    $statuses[] =
                        self::STATUS_COMPLETED;
                }

                break;


            /*
             * Completed
             */
            case self::STATUS_COMPLETED:

                $statuses[] =
                    self::STATUS_CERTIFIED;

                break;
        }


        return $statuses;
    }


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER HELPERS
    |--------------------------------------------------------------------------
    */


    public function getCustomerTypeAttribute(): string
    {
        return
            $this->customer
            && $this->customer->isKplc()

                ? 'Internal (KPLC)'

                : 'External';
    }


    public function getIsKplcCustomerAttribute(): bool
    {
        return (bool) (
            $this->customer
            && $this->customer->isKplc()
        );
    }


    /**
     * Customer name.
     *
     * Uses company_name first because that is the field
     * used throughout the calibration screens.
     */
    public function getCustomerNameAttribute(): string
    {
        if ($this->customer) {

            return
                $this->customer->company_name

                ?? $this->customer->name

                ?? 'N/A';
        }


        return
            $this->attributes['customer_name']

            ?? 'N/A';
    }


    /**
     * Customer phone.
     */
    public function getCustomerPhoneAttribute(): string
    {
        if ($this->customer) {

            return
                $this->customer->phone

                ?? $this->customer->phone_number

                ?? 'N/A';
        }


        return 'N/A';
    }


    /*
    |--------------------------------------------------------------------------
    | QUERY SCOPES
    |--------------------------------------------------------------------------
    */


    public function scopePending($query)
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }


    public function scopeQuoted($query)
    {
        return $query->where(
            'status',
            self::STATUS_QUOTED
        );
    }


    public function scopePaid($query)
    {
        return $query->where(
            'status',
            self::STATUS_PAID
        );
    }


    public function scopeAssigned($query)
    {
        return $query->where(
            'status',
            self::STATUS_ASSIGNED
        );
    }


    public function scopeCompleted($query)
    {
        return $query->where(
            'status',
            self::STATUS_COMPLETED
        );
    }


    public function scopeCertified($query)
    {
        return $query->where(
            'status',
            self::STATUS_CERTIFIED
        );
    }


    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            self::STATUS_REJECTED
        );
    }


    public function scopeByCustomer(
        $query,
        $customerId
    ) {
        return $query->where(
            'customer_id',
            $customerId
        );
    }


    public function scopeByTechnician(
        $query,
        $technicianId
    ) {
        return $query->where(
            'performed_by',
            $technicianId
        );
    }


    public function scopeByLocation(
        $query,
        $location
    ) {
        return $query->where(
            'calibration_location',
            $location
        );
    }


    public function scopeOnSite($query)
    {
        return $query->where(
            'calibration_location',
            'on_site'
        );
    }


    public function scopeLaboratory($query)
    {
        return $query->where(
            'calibration_location',
            'laboratory'
        );
    }


    /**
     * Calibrations requiring payment.
     */
    public function scopeNeedsPayment($query)
    {
        return $query

            ->where(
                'is_external',
                true
            )

            ->where(
                'requires_payment',
                true
            )

            ->where(
                function ($q) {

                    $q
                        ->where(
                            'payment_status',
                            '!=',
                            'paid'
                        )

                        ->orWhereNull(
                            'payment_status'
                        );
                }
            );
    }


    /**
     * Calibrations ready for technician assignment.
     */
    public function scopeReadyForAssignment($query)
    {
        return $query

            ->where(
                function ($q) {

                    $q
                        ->where(
                            'is_external',
                            false
                        )

                        ->orWhere(
                            'requires_payment',
                            false
                        )

                        ->orWhere(
                            'payment_status',
                            'paid'
                        );
                }
            )

            ->whereIn(
                'status',
                [
                    self::STATUS_PENDING,
                    self::STATUS_PAID,
                ]
            );
    }


    /**
     * Filter by batch.
     */
    public function scopeByBatch(
        $query,
        $batchId
    ) {
        return $query->where(
            'batch_id',
            $batchId
        );
    }


    /**
     * Unique batch list.
     */
    public function scopeUniqueBatches($query)
    {
        return $query

            ->whereNotNull(
                'batch_id'
            )

            ->distinct()

            ->select(
                'batch_id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DATE HELPERS
    |--------------------------------------------------------------------------
    */


    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at

            ? $this->created_at
                ->format('Y-m-d H:i')

            : 'N/A';
    }


    public function getFormattedScheduledDateAttribute(): string
    {
        return $this->scheduled_date

            ? $this->scheduled_date
                ->format('Y-m-d H:i')

            : 'Not scheduled';
    }


    public function getFormattedCompletedAtAttribute(): string
    {
        return $this->completed_at

            ? $this->completed_at
                ->format('Y-m-d H:i')

            : 'Pending';
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS FLOW METHODS
    |--------------------------------------------------------------------------
    */


    /**
     * Get applicable workflow.
     */
    public function getStatusFlow(): array
    {
        if (
            $this->customer
            && $this->customer->isKplc()
        ) {
            return self::FLOW_INTERNAL;
        }


        return self::FLOW_EXTERNAL;
    }


    /**
     * Next workflow status.
     */
    public function getNextStatus(): ?string
    {
        $flow =
            $this->getStatusFlow();


        return
            $flow[$this->status]['next']

            ?? null;
    }


    /**
     * Previous workflow status.
     */
    public function getPreviousStatus(): ?string
    {
        $flow =
            $this->getStatusFlow();


        foreach (
            $flow as $status => $data
        ) {

            if (
                ($data['next'] ?? null)
                === $this->status
            ) {
                return $status;
            }
        }


        return null;
    }


    /**
     * Determine whether another workflow step exists.
     */
    public function canTransitionToNext(): bool
    {
        return
            $this->getNextStatus() !== null;
    }


    /**
     * Move calibration to next workflow status.
     */
    public function transitionToNext(): bool
    {
        $nextStatus =
            $this->getNextStatus();


        if (!$nextStatus) {
            return false;
        }


        $this->status =
            $nextStatus;


        return $this->save();
    }


    /**
     * Get workflow progress percentage.
     */
    public function getProgressPercentage(): int
    {
        $statuses = [

            self::STATUS_PENDING,
            self::STATUS_QUOTED,
            self::STATUS_PAID,

            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_TESTING,

            self::STATUS_COMPLETED,
            self::STATUS_CERTIFIED,

        ];


        $currentIndex =
            array_search(
                $this->status,
                $statuses,
                true
            );


        if ($currentIndex === false) {
            return 0;
        }


        $total =
            count($statuses) - 1;


        if ($total <= 0) {
            return 0;
        }


        return (int) round(
            ($currentIndex / $total)
            * 100
        );
    }


    /**
     * Workflow steps for display.
     */
    public function getFlowSteps(): array
    {
        $flow =
            $this->getStatusFlow();

        $steps = [];

        $currentStatus =
            $this->status;


        foreach (
            $flow as $status => $data
        ) {

            $steps[] = [

                'status' =>
                    $status,

                'label' =>
                    $data['label'],

                'is_active' =>
                    $status === $currentStatus,

                'is_completed' =>
                    $this->isStatusCompleted(
                        $status
                    ),

                'is_next' =>
                    $status
                    === $this->getNextStatus(),

            ];
        }


        return $steps;
    }


    /**
     * Determine whether a workflow status has already been completed.
     */
    private function isStatusCompleted(
        string $status
    ): bool {

        $flow =
            $this->getStatusFlow();


        $statuses =
            array_keys($flow);


        $currentIndex =
            array_search(
                $this->status,
                $statuses,
                true
            );


        $statusIndex =
            array_search(
                $status,
                $statuses,
                true
            );


        if (
            $currentIndex === false
            || $statusIndex === false
        ) {
            return false;
        }


        return
            $statusIndex < $currentIndex;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS ATTRIBUTES
    |--------------------------------------------------------------------------
    */


    public function getStatusLabelAttribute(): string
    {
        $flow =
            $this->getStatusFlow();


        return
            $flow[$this->status]['label']

            ?? ucfirst(
                str_replace(
                    '_',
                    ' ',
                    (string) $this->status
                )
            );
    }


    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {

            self::STATUS_PENDING =>
                'warning',

            self::STATUS_QUOTED =>
                'info',

            self::STATUS_PAID =>
                'success',

            self::STATUS_ASSIGNED =>
                'primary',

            self::STATUS_IN_PROGRESS =>
                'info',

            self::STATUS_TESTING =>
                'info',

            self::STATUS_COMPLETED =>
                'success',

            self::STATUS_CERTIFIED =>
                'success',

            self::STATUS_REJECTED =>
                'danger',

            self::STATUS_DISPATCHED =>
                'secondary',

            default =>
                'secondary',

        };
    }


    public function getProgressAttribute(): int
    {
        return
            $this->getProgressPercentage();
    }


    /*
    |--------------------------------------------------------------------------
    | TECHNICIAN / QUALITY HELPERS
    |--------------------------------------------------------------------------
    */


    /**
     * Determine whether logged-in user is the assigned technician.
     */
    public function isAssignedTechnician(): bool
    {
        if (!$this->performed_by) {
            return false;
        }


        return
            auth()->check()

            && (int) auth()->id()
                === (int) $this->performed_by;
    }


    /**
     * Determine whether calibration requires quality checking.
     */
    public function needsQualityCheck(): bool
    {
        return
            $this->status === self::STATUS_TESTING

            && !$this->quality_checked;
    }


    /**
     * Human-readable quality status.
     */
    public function getQualityStatusAttribute(): string
    {
        if ($this->quality_approved) {
            return 'Passed';
        }


        if ($this->quality_rejected) {
            return 'Failed';
        }


        if ($this->quality_checked) {
            return 'Checked - Pending';
        }


        return 'Not Checked';
    }


    /*
    |--------------------------------------------------------------------------
    | TECHNICIAN ASSIGNMENT / REASSIGNMENT
    |--------------------------------------------------------------------------
    */


    /**
     * Assign or reassign technician.
     */
    public function assignTechnician(
        $technicianId,
        $reassignmentReason = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Payment validation
        |--------------------------------------------------------------------------
        */

        if (
            $this->is_external
            && $this->requires_payment
            && !$this->isPaymentCompleted()
        ) {

            return [

                'success' => false,

                'message' =>
                    'Payment must be completed before assigning a technician.',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Technician validation
        |--------------------------------------------------------------------------
        */

        $technician =
            User::find(
                $technicianId
            );


        if (!$technician) {

            return [

                'success' => false,

                'message' =>
                    'Technician not found.',

            ];
        }


        if (
            !$technician->hasAnyRole([
                'technician',
                'lab_technician',
                'supervisor',
            ])
        ) {

            return [

                'success' => false,

                'message' =>
                    'Selected user is not a valid technician.',

            ];
        }


        if (
            isset($technician->is_active)
            && !$technician->is_active
        ) {

            return [

                'success' => false,

                'message' =>
                    'Selected technician is not active.',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Determine assignment type
        |--------------------------------------------------------------------------
        */

        $oldTechnicianId =
            $this->performed_by;


        $isReassignment =

            !is_null($oldTechnicianId)

            && (int) $oldTechnicianId
                !== (int) $technicianId;


        $isSameTechnician =

            !is_null($oldTechnicianId)

            && (int) $oldTechnicianId
                === (int) $technicianId;


        if ($isSameTechnician) {

            return [

                'success' => true,

                'message' =>
                    "{$technician->name} is already assigned.",

                'is_reassignment' =>
                    false,

            ];
        }


        if (
            $isReassignment
            && empty($reassignmentReason)
        ) {

            return [

                'success' => false,

                'message' =>
                    'A reassignment reason is required when changing the technician.',

            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Save assignment
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            $this->update([

                'performed_by' =>
                    $technicianId,

                'status' =>
                    self::STATUS_ASSIGNED,

                'assigned_at' =>
                    now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Reassignment information
            |--------------------------------------------------------------------------
            */

            if ($isReassignment) {

                $this->update([

                    'previous_technician_id' =>
                        $oldTechnicianId,

                    'reassigned_at' =>
                        now(),

                    'reassigned_by' =>
                        auth()->id(),

                ]);


                ReassignmentHistory::create([

                    'calibration_id' =>
                        $this->id,

                    'from_technician_id' =>
                        $oldTechnicianId,

                    'to_technician_id' =>
                        $technicianId,

                    'reassigned_by' =>
                        auth()->id(),

                    'reason' =>
                        $reassignmentReason,

                    'reassigned_at' =>
                        now(),

                ]);


                Log::info(
                    'Calibration reassigned',
                    [

                        'calibration_id' =>
                            $this->id,

                        'from_technician_id' =>
                            $oldTechnicianId,

                        'from_technician' =>
                            optional(
                                User::find(
                                    $oldTechnicianId
                                )
                            )->name,

                        'to_technician_id' =>
                            $technicianId,

                        'to_technician' =>
                            $technician->name,

                        'reassigned_by' =>
                            auth()->id(),

                        'reason' =>
                            $reassignmentReason,

                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Job Card
            |--------------------------------------------------------------------------
            */

            if ($this->jobCard) {

                $this->jobCard->update([

                    'allocated_to' =>
                        $technicianId,

                    'status' =>
                        'allocated',

                    'assigned_at' =>
                        now(),

                ]);
            }


            DB::commit();


            return [

                'success' =>
                    true,

                'message' =>
                    $isReassignment

                        ? "Work reassigned to {$technician->name} successfully."

                        : "Technician {$technician->name} assigned successfully.",

                'is_reassignment' =>
                    $isReassignment,

            ];


        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error(
                'Failed to assign technician',
                [

                    'calibration_id' =>
                        $this->id,

                    'technician_id' =>
                        $technicianId,

                    'error' =>
                        $e->getMessage(),

                    'trace' =>
                        $e->getTraceAsString(),

                ]
            );


            return [

                'success' =>
                    false,

                'message' =>
                    'Failed to assign technician: '
                    . $e->getMessage(),

            ];
        }
    }


    /**
     * Get reassignment history.
     */
    public function getReassignmentHistory()
    {
        return $this

            ->reassignmentHistories()

            ->with([
                'fromTechnician',
                'toTechnician',
                'reassignedBy',
            ])

            ->orderBy(
                'reassigned_at',
                'desc'
            )

            ->get();
    }


    /**
     * Determine whether calibration has been reassigned.
     */
    public function getIsReassignedAttribute(): bool
    {
        return
            $this
                ->reassignmentHistories()
                ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | JOB CARD HELPERS
    |--------------------------------------------------------------------------
    */


    /**
     * Create or update related job card.
     *
     * Retained from existing model for compatibility.
     */
    private function createJobCard(
        $technicianId
    ) {

        $jobCard =
            JobCard::where(
                'calibration_id',
                $this->id
            )->first();


        if (!$jobCard) {

            $customerRequest =
                CustomerRequest::where(
                    'calibration_id',
                    $this->id
                )->first();


            $jobCardData = [

                'calibration_id' =>
                    $this->id,

                'customer_id' =>
                    $this->customer_id,

                'customer_request_id' =>
                    $customerRequest?->id,

                'job_card_number' =>
                    JobCard::generateJobCardNumber(),

                'job_number' =>
                    JobCard::generateJobCardNumber(),

                'status' =>
                    'allocated',

                'description' =>
                    'Job card for calibration #'
                    . $this->id,

                'allocated_to' =>
                    $technicianId,

                'allocated_by' =>
                    auth()->id(),

                'assigned_at' =>
                    now(),

                'allocated_at' =>
                    now(),

            ];


            $jobCard =
                JobCard::create(
                    $jobCardData
                );


        } else {

            $jobCard->update([

                'status' =>
                    'allocated',

                'allocated_to' =>
                    $technicianId,

                'allocated_by' =>
                    auth()->id(),

                'assigned_at' =>
                    now(),

                'allocated_at' =>
                    now(),

            ]);
        }


        return $jobCard;
    }


    /**
     * Get linked customer request ID.
     */
    private function getCustomerRequestId()
    {
        return CustomerRequest::where(
            'calibration_id',
            $this->id
        )->value('id');
    }


    /*
    |--------------------------------------------------------------------------
    | BATCH HELPERS
    |--------------------------------------------------------------------------
    */


    /**
     * Number of distinct calibration batches.
     */
    public static function getBatchCount(): int
    {
        try {

            return self::query()

                ->whereNotNull(
                    'batch_id'
                )

                ->distinct()

                ->count(
                    'batch_id'
                );


        } catch (\Throwable) {

            return 0;
        }
    }


    /**
     * Check whether batch_id column exists.
     */
    public static function hasBatchIdColumn(): bool
    {
        try {

            return Schema::hasColumn(
                'calibrations',
                'batch_id'
            );


        } catch (\Throwable) {

            return false;
        }
    }

    public function quoteRequest()
{
    return $this->belongsTo(PublicQuoteRequest::class, 'quote_request_id');
}

public function items()
{
    return $this->hasMany(CalibrationItem::class)
        ->orderBy('position');
}

public function serviceType()
{
    return $this->belongsTo(KplcServiceType::class, 'service_type_id');
}

public function quotes(): HasMany
{
    return $this->hasMany(\App\Models\Quote::class, 'calibration_id');
}
}
