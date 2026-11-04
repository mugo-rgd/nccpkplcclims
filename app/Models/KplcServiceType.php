<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KplcServiceType extends Model
{
    protected $table = 'kplc_service_types';

    protected $fillable = [
        'serv_code',
        'serv_name',
        'serv_method',
        'notes',
        'comments',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Only active services.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Full service description.
     */
    public function getFullDescriptionAttribute(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->serv_code,
            $this->serv_name,
            $this->serv_method
        );
    }
}
