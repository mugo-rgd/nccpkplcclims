<?php

namespace App\Services;

use App\Models\TestResult;
use Illuminate\Support\Facades\Log;

class TrialDataService
{
    /**
     * Get trial data as array with proper numeric values
     */
    public static function getTrialData(TestResult $testResult): array
    {
        if (empty($testResult->trial_data)) {
            return [];
        }

        try {
            // Check if trial_data is already an array (Laravel casts JSON columns)
            if (is_array($testResult->trial_data)) {
                $data = $testResult->trial_data;
            } else if (is_string($testResult->trial_data)) {
                $data = json_decode($testResult->trial_data, true);
            } else {
                return [];
            }

            if (!is_array($data)) {
                return [];
            }

            // Ensure all values are numeric
            return array_map(function($value) {
                return is_numeric($value) ? floatval($value) : $value;
            }, $data);

        } catch (\Exception $e) {
            Log::error("Failed to parse trial_data for ID {$testResult->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Format trial data for display
     */
    public static function formatForDisplay(TestResult $testResult, int $decimalPlaces = 4): string
    {
        $data = self::getTrialData($testResult);

        if (empty($data)) {
            return 'No detailed test results available';
        }

        return implode(', ', array_map(function($value) use ($decimalPlaces) {
            return number_format(floatval($value), $decimalPlaces);
        }, $data));
    }

    /**
     * Calculate statistics from trial data
     */
    public static function calculateStatistics(TestResult $testResult): array
    {
        $data = self::getTrialData($testResult);

        if (empty($data)) {
            return [
                'count' => 0,
                'mean' => null,
                'min' => null,
                'max' => null,
                'std_dev' => null,
                'data' => [],
            ];
        }

        $numericData = array_map('floatval', $data);
        $count = count($numericData);
        $sum = array_sum($numericData);
        $mean = $sum / $count;
        $min = min($numericData);
        $max = max($numericData);

        // Calculate standard deviation
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $numericData)) / $count;

        $stdDev = sqrt($variance);

        return [
            'count' => $count,
            'mean' => $mean,
            'min' => $min,
            'max' => $max,
            'std_dev' => $stdDev,
            'data' => $numericData,
        ];
    }

    /**
     * Convert trial data from strings to numbers
     */
    public static function convertToNumbers(array $data): array
    {
        return array_map(function($value) {
            return is_numeric($value) ? floatval($value) : $value;
        }, $data);
    }

    /**
     * Validate trial data structure
     */
    public static function isValid(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        foreach ($data as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        return true;
    }
}
