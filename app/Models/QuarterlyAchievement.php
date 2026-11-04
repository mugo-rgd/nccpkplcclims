<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuarterlyAchievement extends Model
{
    protected $table = 'quarterly_achievements';

    protected $fillable = [
        'objective_id',
        'fyr_period_id',
        'quarter',
        'quarter_label',
        'actual_achievement',
        'data_collection_date',
        'collected_by_id',
        'remarks',
        'evidence_url',
        'alert_sent'
    ];

    protected $casts = [
        'actual_achievement' => 'decimal:2',
        'data_collection_date' => 'date',
        'alert_sent' => 'boolean'
    ];

    // FIX: Explicitly specify foreign key and local key
    public function objective()
    {
        return $this->belongsTo(QualityObjective::class, 'objective_id', 'id');
    }

    public function fyrPeriod()
    {
        return $this->belongsTo(QualityFyrPeriod::class, 'fyr_period_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_id');
    }

    // Accessors
    public function getAchievementPercentageAttribute()
    {
        if (!$this->objective || $this->objective->target_value == 0) {
            return null;
        }

        if (in_array($this->objective->unit_of_measure, ['Days', 'Hours'])) {
            return round(($this->objective->target_value / $this->actual_achievement) * 100, 2);
        }

        return round(($this->actual_achievement / $this->objective->target_value) * 100, 2);
    }

    public function getStatusAttribute()
    {
        $percentage = $this->achievement_percentage;
        if ($percentage === null) return 'Pending';

        if ($percentage >= 95) return 'On Track';
        if ($percentage >= 80) return 'At Risk';
        return 'Not Met';
    }

    // Scopes
    public function scopeCurrentFYR($query)
    {
        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        if ($currentFyr) {
            return $query->where('fyr_period_id', $currentFyr->id);
        }
        return $query;
    }

    public function scopeByQuarter($query, $quarter)
    {
        return $query->where('quarter', $quarter);
    }
}
