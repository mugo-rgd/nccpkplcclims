<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityFyrPeriod;
use Carbon\Carbon;

class QualityFyrPeriodSeeder extends Seeder
{
    public function run()
    {
        $currentYear = Carbon::now()->year;

        // Create FYR periods from 2020 to 2030
        for ($year = 2020; $year <= 2030; $year++) {
            $fyr = $year . '-' . ($year + 1);

            // Determine status
            $isActive = false;
            $isClosed = false;
            $notes = 'Future FYR';

            if ($year == $currentYear - 1 || $year == $currentYear) {
                $isActive = true;
                $notes = 'Current FYR';
            }

            if ($year < $currentYear - 1) {
                $isClosed = true;
                $notes = 'Closed FYR';
            }

            QualityFyrPeriod::firstOrCreate(
                ['fyr' => $fyr],
                [
                    'display_name' => 'FYR ' . $year . '/' . ($year + 1),
                    'start_date' => Carbon::create($year, 7, 1),
                    'end_date' => Carbon::create($year + 1, 6, 30)->endOfMonth(),
                    'is_active' => $isActive,
                    'is_closed' => $isClosed,
                    'quarters' => [
                        [
                            'quarter' => 'Q1',
                            'label' => 'Sept ' . $year,
                            'start_month' => 7,
                            'end_month' => 9
                        ],
                        [
                            'quarter' => 'Q2',
                            'label' => 'Dec ' . $year,
                            'start_month' => 10,
                            'end_month' => 12
                        ],
                        [
                            'quarter' => 'Q3',
                            'label' => 'Mar ' . ($year + 1),
                            'start_month' => 1,
                            'end_month' => 3
                        ],
                        [
                            'quarter' => 'Q4',
                            'label' => 'June ' . ($year + 1),
                            'start_month' => 4,
                            'end_month' => 6
                        ]
                    ],
                    'notes' => $notes
                ]
            );
        }
    }
}
