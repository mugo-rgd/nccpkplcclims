<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityObjective extends Model
{
    use SoftDeletes;

    protected $table = 'quality_objectives';

    protected $fillable = [
        'objective_id',
        'section',
        'description',
        'unit_of_measure',
        'baseline_value',
        'baseline_fyr',
        'target_value',
        'target_fyr',
        'historical_targets',
        'owner_id',
        'status',
        'iso17025_clause',
        'iso17025_requirement',
        'remarks'
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'historical_targets' => 'array',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // FIX: Explicitly specify the foreign key
    public function achievements()
    {
        return $this->hasMany(QuarterlyAchievement::class, 'objective_id');
    }

    public function fyrPeriod()
    {
        return $this->belongsTo(QualityFyrPeriod::class, 'target_fyr', 'fyr');
    }

    public function baselineFyrPeriod()
    {
        return $this->belongsTo(QualityFyrPeriod::class, 'baseline_fyr', 'fyr');
    }

   public function correctiveActions()
{
    return $this->hasMany(CorrectiveAction::class, 'objective_id', 'id');
}
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    public function scopeByFYR($query, $fyr)
    {
        return $query->where('target_fyr', $fyr);
    }

    public function scopeCurrentFYR($query)
    {
        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        if ($currentFyr) {
            return $query->where('target_fyr', $currentFyr->fyr);
        }
        return $query;
    }

    // Accessors
    public function getSectionsAttribute()
    {
        return [
            'MCL' => 'MCL Quality Objectives',
            'Instruments' => 'Instruments and Standards',
            'Large Power' => 'Large Power and Meter Testing'
        ];
    }

    public function getLatestAchievementAttribute()
    {
        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        if (!$currentFyr) {
            return null;
        }

        return $this->achievements()
            ->where('fyr_period_id', $currentFyr->id)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getAchievementPercentageAttribute()
    {
        $latest = $this->latest_achievement;
        if (!$latest || $this->target_value == 0) {
            return null;
        }

        if (in_array($this->unit_of_measure, ['Days', 'Hours'])) {
            return round(($this->target_value / $latest->actual_achievement) * 100, 2);
        }

        return round(($latest->actual_achievement / $this->target_value) * 100, 2);
    }

    public function getStatusAttribute()
    {
        $percentage = $this->achievement_percentage;
        if ($percentage === null) return 'Pending';

        if ($percentage >= 95) return 'On Track';
        if ($percentage >= 80) return 'At Risk';
        return 'Not Met';
    }

    // Methods for handling targets across years
    public function getTargetForFYR($fyr)
    {
        if ($this->target_fyr == $fyr) {
            return $this->target_value;
        }

        if ($this->historical_targets && isset($this->historical_targets[$fyr])) {
            return $this->historical_targets[$fyr];
        }

        return null;
    }

    public function setTargetForFYR($fyr, $value)
    {
        $historical = $this->historical_targets ?? [];
        $historical[$fyr] = $value;
        $this->historical_targets = $historical;
        $this->save();
    }

    public function getHistoricalTargetsAttribute()
    {
        $targets = $this->attributes['historical_targets'] ?? '[]';
        $historical = json_decode($targets, true) ?? [];

        if ($this->target_fyr && !isset($historical[$this->target_fyr])) {
            $historical[$this->target_fyr] = $this->target_value;
        }

        return $historical;
    }

    public function getTargetTrendAttribute()
    {
        $trend = [];
        $historical = $this->historical_targets;

        ksort($historical);

        foreach ($historical as $fyr => $value) {
            $trend[] = [
                'fyr' => $fyr,
                'target' => $value,
                'achievement' => $this->getAchievementForFYR($fyr)
            ];
        }

        return $trend;
    }

    public function getAchievementForFYR($fyr)
    {
        $fyrPeriod = QualityFyrPeriod::where('fyr', $fyr)->first();
        if (!$fyrPeriod) {
            return null;
        }

        $achievement = $this->achievements()
            ->where('fyr_period_id', $fyrPeriod->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return $achievement ? $achievement->actual_achievement : null;
    }
}
