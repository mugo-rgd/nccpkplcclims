<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
       'name',
    'email',
    'staff_number',
    'phone_number',
    'signature_path',
    'signature_filename',
    'use_auto_signature',
    'signature_updated_at',
    'station',
    'status',
    'password',
    'role',
    'is_active',
    'last_login_at',
    'last_activity_at',
    'session_id',
    'legacy_role',
    'designation',
    'department',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'use_auto_signature' => 'boolean',
            'status' => 'boolean',
            'last_login_at' => 'datetime',
            'signature_updated_at' => 'datetime',
        ];
    }

    /**
     * Get the customer associated with the user.
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Check if user is a KPLC staff member.
     */
    public function isKplcStaff(): bool
    {
        return !is_null($this->staff_number) && $this->staff_number !== '';
    }

    /**
     * Check if user has a signature.
     */
    public function hasSignature(): bool
    {
        return !is_null($this->signature_path) && file_exists(storage_path('app/public/' . $this->signature_path));
    }

    /**
     * Get signature URL.
     */
    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->signature_path) {
            return asset('storage/' . $this->signature_path);
        }
        return null;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', true);
    }

    /**
     * Scope a query to only include KPLC staff.
     */
    public function scopeKplcStaff($query)
    {
        return $query->whereNotNull('staff_number');
    }

    /**
     * Get full display name with staff number.
     */
    public function getFullDisplayAttribute(): string
    {
        $display = $this->name;
        if ($this->staff_number) {
            $display .= " ({$this->staff_number})";
        }
        return $display;
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        if ($this->hasRole('super_admin')) {
            return 'Super Administrator';
        }
        if ($this->hasRole('lab_manager')) {
            return 'Lab Manager';
        }
        if ($this->hasRole('lab_technician')) {
            return 'Lab Technician';
        }
        if ($this->hasRole('quality_assurance')) {
            return 'Quality Assurance';
        }
        if ($this->hasRole('customer_service')) {
            return 'Customer Service';
        }
        if ($this->hasRole('customer')) {
            return 'Customer';
        }
        return ucfirst($this->role ?? 'Unknown');
    }

    /**
     * Determine if user can sign certificates.
     */
    public function canSignCertificates(): bool
    {
        return $this->hasAnyRole(['lab_manager', 'quality_assurance', 'supervisor']);
    }

    public function qualityChecks()
{
    return $this->hasMany(Calibration::class, 'quality_checked_by');
}

// In your User model
public function reassignedFrom()
{
    return $this->hasMany(ReassignmentHistory::class, 'from_technician_id');
}

public function reassignedTo()
{
    return $this->hasMany(ReassignmentHistory::class, 'to_technician_id');
}

public function reassignedBy()
{
    return $this->hasMany(ReassignmentHistory::class, 'reassigned_by');
}
// Add chat relationships
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_at', 'is_admin', 'is_active', 'joined_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function unreadMessages()
    {
        return $this->messages()->where('is_read', false);
    }

    public function status()
    {
        return $this->hasOne(UserStatus::class);
    }

    public function getAvatarAttribute()
    {
        return $this->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=fff&size=40';
    }

    public function getOnlineStatusAttribute()
    {
        return $this->status?->status ?? 'offline';
    }

    public function getLastSeenAttribute()
    {
        return $this->status?->last_seen_at ?? $this->created_at;
    }

    public function getUnreadCountAttribute()
    {
        return $this->messages()->where('is_read', false)->count();
    }

    // Scope for online users
    public function scopeOnline($query)
    {
        return $query->whereHas('status', function($q) {
            $q->where('status', 'online');
        });
    }

    public function performedCalibrations()
{
    return $this->hasMany(
        Calibration::class,
        'performed_by'
    );
}

public function generatedCertificates()
{
    return $this->hasMany(
        Certificate::class,
        'generated_by'
    );
}

public function checkedCertificates()
{
    return $this->hasMany(
        Certificate::class,
        'checked_by'
    );
}

public function approvedCertificates()
{
    return $this->hasMany(
        Certificate::class,
        'approved_by'
    );
}

public function issuedCertificates()
{
    return $this->hasMany(
        Certificate::class,
        'issued_by'
    );
}

public function sentCertificates()
{
    return $this->hasMany(
        Certificate::class,
        'sent_by'
    );
}
}
