<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QualityFyrPeriod extends Model
{
    protected $table = 'quality_fyr_periods';

    protected $fillable = [
        'fyr',
        'display_name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'quarters',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'quarters' => 'array'
    ];

    // Relationships
    public function objectives()
    {
        return $this->hasMany(QualityObjective::class, 'target_fyr', 'fyr');
    }

    public function achievements()
    {
        return $this->hasMany(QuarterlyAchievement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    // Accessors
    public function getQuartersListAttribute()
    {
        if ($this->quarters) {
            return $this->quarters;
        }

        $startYear = date('Y', strtotime($this->start_date));
        $endYear = date('Y', strtotime($this->end_date));

        return [
            [
                'quarter' => 'Q1',
                'label' => 'Sept ' . $startYear,
                'start_month' => 7,
                'end_month' => 9
            ],
            [
                'quarter' => 'Q2',
                'label' => 'Dec ' . $startYear,
                'start_month' => 10,
                'end_month' => 12
            ],
            [
                'quarter' => 'Q3',
                'label' => 'Mar ' . $endYear,
                'start_month' => 1,
                'end_month' => 3
            ],
            [
                'quarter' => 'Q4',
                'label' => 'June ' . $endYear,
                'start_month' => 4,
                'end_month' => 6
            ]
        ];
    }

    public function getCurrentQuarterAttribute()
    {
        $now = Carbon::now();
        $quarters = $this->quarters_list;

        foreach ($quarters as $q) {
            $startMonth = $q['start_month'];
            $endMonth = $q['end_month'];

            $year = ($q['quarter'] == 'Q1' || $q['quarter'] == 'Q2')
                ? date('Y', strtotime($this->start_date))
                : date('Y', strtotime($this->end_date));

            $startDate = Carbon::create($year, $startMonth, 1);
            $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();

            if ($now->between($startDate, $endDate)) {
                return $q;
            }
        }

        return $quarters[0];
    }

    // Static Methods
    public static function getCurrentFYR()
    {
        return self::where('is_active', true)
            ->where('is_closed', false)
            ->first();
    }

    public static function getFYR($fyr)
    {
        return self::where('fyr', $fyr)->first();
    }

    public static function createFromYear($startYear)
    {
        $endYear = $startYear + 1;
        $fyr = $startYear . '-' . $endYear;

        return self::create([
            'fyr' => $fyr,
            'display_name' => 'FYR ' . $startYear . '/' . $endYear,
            'start_date' => Carbon::create($startYear, 7, 1),
            'end_date' => Carbon::create($endYear, 6, 30)->endOfMonth(),
            'is_active' => false,
            'is_closed' => false
        ]);
    }

    public function getNextFYR()
    {
        $years = explode('-', $this->fyr);
        $nextStart = $years[0] + 1;
        $nextEnd = $years[1] + 1;

        return self::where('fyr', $nextStart . '-' . $nextEnd)->first();
    }

    public function getPreviousFYR()
    {
        $years = explode('-', $this->fyr);
        $prevStart = $years[0] - 1;
        $prevEnd = $years[1] - 1;

        return self::where('fyr', $prevStart . '-' . $prevEnd)->first();
    }
}
