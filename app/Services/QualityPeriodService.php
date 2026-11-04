<?php

namespace App\Services;

use App\Models\QualityFyrPeriod;
use App\Models\QualityObjective;
use Carbon\Carbon;

class QualityPeriodService
{
    /**
     * Initialize FYR periods
     */
    public static function initializeFYRs()
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 2;

        for ($year = $startYear; $year <= $currentYear + 2; $year++) {
            QualityFyrPeriod::firstOrCreate(
                ['fyr' => $year . '-' . ($year + 1)],
                [
                    'display_name' => 'FYR ' . $year . '/' . ($year + 1),
                    'start_date' => Carbon::create($year, 7, 1),
                    'end_date' => Carbon::create($year + 1, 6, 30)->endOfMonth(),
                    'is_active' => ($year == $currentYear - 1 || $year == $currentYear),
                    'is_closed' => ($year < $currentYear - 1)
                ]
            );
        }
    }

    /**
     * Get current FYR period
     */
    public static function getCurrentFYR()
    {
        return QualityFyrPeriod::getCurrentFYR();
    }

    /**
     * Get all FYR periods for dropdown
     */
    public static function getFYRDropdown()
    {
        return QualityFyrPeriod::orderBy('start_date', 'desc')
            ->pluck('display_name', 'fyr')
            ->toArray();
    }

    /**
     * Get current quarter
     */
    public static function getCurrentQuarter()
    {
        $currentFyr = self::getCurrentFYR();
        if (!$currentFyr) {
            return null;
        }

        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $quarters = [
            'Q1' => ['months' => [7, 8, 9], 'label' => 'Sept'],
            'Q2' => ['months' => [10, 11, 12], 'label' => 'Dec'],
            'Q3' => ['months' => [1, 2, 3], 'label' => 'Mar'],
            'Q4' => ['months' => [4, 5, 6], 'label' => 'June']
        ];

        foreach ($quarters as $key => $quarter) {
            if (in_array($month, $quarter['months'])) {
                if ($key == 'Q1' || $key == 'Q2') {
                    $displayYear = ($month >= 7) ? $year : $year - 1;
                } else {
                    $displayYear = ($month < 7) ? $year : $year + 1;
                }

                return [
                    'quarter' => $key,
                    'label' => $quarter['label'] . ' ' . $displayYear,
                    'start_month' => $quarter['months'][0],
                    'end_month' => end($quarter['months'])
                ];
            }
        }

        return null;
    }

    /**
     * Generate next year targets
     */
    public static function generateNextYearTargets()
    {
        $currentFyr = self::getCurrentFYR();
        if (!$currentFyr) {
            return false;
        }

        $nextFyr = $currentFyr->getNextFYR();
        if (!$nextFyr) {
            return false;
        }

        // Get all current objectives
        $objectives = QualityObjective::where('target_fyr', $currentFyr->fyr)->get();

        $created = 0;
        $updated = 0;

        foreach ($objectives as $objective) {
            // Calculate next target
            $nextTarget = $objective->target_value;

            if (in_array($objective->unit_of_measure, ['Days', 'Hours'])) {
                $nextTarget = round($nextTarget * 0.95, 2);
            } else {
                $nextTarget = round($nextTarget * 1.05, 2);
            }

            // Store current target in historical data
            $historical = $objective->historical_targets ?? [];
            $historical[$currentFyr->fyr] = $objective->target_value;

            // Check if objective exists for next FYR
            $existing = QualityObjective::where('objective_id', $objective->objective_id)
                ->where('target_fyr', $nextFyr->fyr)
                ->first();

            if ($existing) {
                // Update existing objective
                $existing->update([
                    'baseline_value' => $objective->target_value,
                    'baseline_fyr' => $currentFyr->fyr,
                    'target_value' => $nextTarget,
                    'historical_targets' => $historical,
                    'owner_id' => $objective->owner_id,
                    'status' => 'active',
                    'remarks' => 'Auto-updated from ' . $currentFyr->fyr
                ]);
                $updated++;
            } else {
                // Create new objective
                QualityObjective::create([
                    'objective_id' => $objective->objective_id,
                    'section' => $objective->section,
                    'description' => $objective->description,
                    'unit_of_measure' => $objective->unit_of_measure,
                    'baseline_value' => $objective->target_value,
                    'baseline_fyr' => $currentFyr->fyr,
                    'target_value' => $nextTarget,
                    'target_fyr' => $nextFyr->fyr,
                    'historical_targets' => $historical,
                    'owner_id' => $objective->owner_id,
                    'status' => 'active',
                    'iso17025_clause' => $objective->iso17025_clause,
                    'iso17025_requirement' => $objective->iso17025_requirement,
                    'remarks' => 'Auto-generated from ' . $currentFyr->fyr
                ]);
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => $objectives->count()
        ];
    }

}
