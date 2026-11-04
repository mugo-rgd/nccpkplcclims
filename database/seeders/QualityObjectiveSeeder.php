<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QualityObjective;
use App\Models\QualityFyrPeriod;
use App\Models\User;

class QualityObjectiveSeeder extends Seeder
{
    public function run()
    {
        // Get current FYR
        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        if (!$currentFyr) {
            $this->command->error('No active FYR found. Run QualityFyrPeriodSeeder first.');
            return;
        }

        // Get previous FYR for baseline
        $previousFyr = $currentFyr->getPreviousFYR();

        // Get a user to assign as owner (assuming user with ID 1 exists)
        $owner = User::first() ?? User::factory()->create();

        $objectives = [
            // MCL Objectives
            [
                'objective_id' => 'MCL-01',
                'section' => 'MCL',
                'description' => 'New single phase meter batch (25,000) testing rate',
                'unit_of_measure' => 'Days',
                'baseline_value' => 6.4,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 6.3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'MCL-02',
                'section' => 'MCL',
                'description' => 'Turnaround time for testing and programming of large power meters',
                'unit_of_measure' => 'Days',
                'baseline_value' => 1.43,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 1.3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'MCL-03',
                'section' => 'MCL',
                'description' => 'Turnaround Testing and Calibration of External Customer Energy Meters and Instruments',
                'unit_of_measure' => 'Days',
                'baseline_value' => 3.92,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 3.6,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'MCL-04',
                'section' => 'MCL',
                'description' => 'Number of Surveillance Checks on Energy Meters',
                'unit_of_measure' => 'NO.',
                'baseline_value' => 35,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 40,
                'owner_id' => $owner->id,
                'iso17025_clause' => '8.2',
                'iso17025_requirement' => 'Management system documentation - monitoring'
            ],
            [
                'objective_id' => 'MCL-05',
                'section' => 'MCL',
                'description' => 'New Business Opportunities Sourced and Completed',
                'unit_of_measure' => 'NO.',
                'baseline_value' => 27,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 30,
                'owner_id' => $owner->id,
                'iso17025_clause' => '4.1',
                'iso17025_requirement' => 'Impartiality & confidentiality - continuous improvement'
            ],
            [
                'objective_id' => 'MCL-06',
                'section' => 'MCL',
                'description' => 'Number of processes developed or reviewed',
                'unit_of_measure' => 'NO.',
                'baseline_value' => 32,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 35,
                'owner_id' => $owner->id,
                'iso17025_clause' => '8.3',
                'iso17025_requirement' => 'Control of management system documents'
            ],
            [
                'objective_id' => 'MCL-07',
                'section' => 'MCL',
                'description' => 'Implementation of Performance Management',
                'unit_of_measure' => '%',
                'baseline_value' => 83.3,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 100,
                'owner_id' => $owner->id,
                'iso17025_clause' => '5.2',
                'iso17025_requirement' => 'Personnel competence & performance evaluation'
            ],

            // Instruments Objectives
            [
                'objective_id' => 'INS-01',
                'section' => 'Instruments',
                'description' => 'Turnaround Testing and Calibration Internal Customer Instruments',
                'unit_of_measure' => 'Days',
                'baseline_value' => 6.43,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 6.3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '6.5',
                'iso17025_requirement' => 'Metrological traceability'
            ],
            [
                'objective_id' => 'INS-02',
                'section' => 'Instruments',
                'description' => 'Turnaround Testing and Calibration of External Customer Energy Meters and Instruments',
                'unit_of_measure' => 'Days',
                'baseline_value' => 3.92,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 3.6,
                'owner_id' => $owner->id,
                'iso17025_clause' => '6.5',
                'iso17025_requirement' => 'Metrological traceability'
            ],
            [
                'objective_id' => 'INS-03',
                'section' => 'Instruments',
                'description' => 'Analysis of failure reasons of various instrument brands',
                'unit_of_measure' => 'Number',
                'baseline_value' => 1,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 2,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.10',
                'iso17025_requirement' => 'Nonconforming work management'
            ],
            [
                'objective_id' => 'INS-04',
                'section' => 'Instruments',
                'description' => 'Compliance to Calibration of Working Standards schedule',
                'unit_of_measure' => '%',
                'baseline_value' => 92.46,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 100,
                'owner_id' => $owner->id,
                'iso17025_clause' => '6.4',
                'iso17025_requirement' => 'Equipment - calibration intervals'
            ],
            [
                'objective_id' => 'INS-05',
                'section' => 'Instruments',
                'description' => 'Intermediate checks of standards',
                'unit_of_measure' => 'Number',
                'baseline_value' => null,
                'baseline_fyr' => null,
                'target_value' => 3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '6.4.10',
                'iso17025_requirement' => 'Equipment - intermediate checks'
            ],
            [
                'objective_id' => 'INS-06',
                'section' => 'Instruments',
                'description' => 'Number of processes reviewed or updated',
                'unit_of_measure' => 'Number',
                'baseline_value' => 20,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 16,
                'owner_id' => $owner->id,
                'iso17025_clause' => '8.3',
                'iso17025_requirement' => 'Control of management system documents'
            ],
            [
                'objective_id' => 'INS-07',
                'section' => 'Instruments',
                'description' => 'Implementation of Performance Management',
                'unit_of_measure' => '%',
                'baseline_value' => 0,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 100,
                'owner_id' => $owner->id,
                'iso17025_clause' => '5.2',
                'iso17025_requirement' => 'Personnel competence & performance evaluation'
            ],

            // Large Power Objectives
            [
                'objective_id' => 'LP-01',
                'section' => 'Large Power',
                'description' => 'New single phase meter batch(25,000) testing rate',
                'unit_of_measure' => 'Days',
                'baseline_value' => 6.4,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 6.3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'LP-02',
                'section' => 'Large Power',
                'description' => 'Turnaround time for testing and programming of large power meters',
                'unit_of_measure' => 'Days',
                'baseline_value' => 1.4,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 1.3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'LP-03',
                'section' => 'Large Power',
                'description' => 'Turnaround time for Testing and Calibration of External Customer Energy Meters',
                'unit_of_measure' => 'Days',
                'baseline_value' => 3.64,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 3.5,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.7',
                'iso17025_requirement' => 'Ensuring validity of results - timely delivery'
            ],
            [
                'objective_id' => 'LP-04',
                'section' => 'Large Power',
                'description' => 'Analysis of failure reasons of new meters and returned large power meters',
                'unit_of_measure' => 'Number',
                'baseline_value' => null,
                'baseline_fyr' => null,
                'target_value' => 3,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.10',
                'iso17025_requirement' => 'Nonconforming work management'
            ],
            [
                'objective_id' => 'LP-05',
                'section' => 'Large Power',
                'description' => 'Quality assurance of test Systems',
                'unit_of_measure' => 'Number',
                'baseline_value' => 2,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 6,
                'owner_id' => $owner->id,
                'iso17025_clause' => '7.2',
                'iso17025_requirement' => 'Selection, verification & validation of methods'
            ],
            [
                'objective_id' => 'LP-06',
                'section' => 'Large Power',
                'description' => 'Number of processes reviewed or updated',
                'unit_of_measure' => 'Number',
                'baseline_value' => 23,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 7,
                'owner_id' => $owner->id,
                'iso17025_clause' => '8.3',
                'iso17025_requirement' => 'Control of management system documents'
            ],
            [
                'objective_id' => 'LP-07',
                'section' => 'Large Power',
                'description' => 'Implementation of Performance Management',
                'unit_of_measure' => '%',
                'baseline_value' => 70,
                'baseline_fyr' => $previousFyr ? $previousFyr->fyr : null,
                'target_value' => 100,
                'owner_id' => $owner->id,
                'iso17025_clause' => '5.2',
                'iso17025_requirement' => 'Personnel competence & performance evaluation'
            ]
        ];

        foreach ($objectives as $objectiveData) {
            QualityObjective::create([
                'objective_id' => $objectiveData['objective_id'],
                'section' => $objectiveData['section'],
                'description' => $objectiveData['description'],
                'unit_of_measure' => $objectiveData['unit_of_measure'],
                'baseline_value' => $objectiveData['baseline_value'],
                'baseline_fyr' => $objectiveData['baseline_fyr'],
                'target_value' => $objectiveData['target_value'],
                'target_fyr' => $currentFyr->fyr,
                'historical_targets' => [
                    $currentFyr->fyr => $objectiveData['target_value']
                ],
                'owner_id' => $objectiveData['owner_id'],
                'status' => 'active',
                'iso17025_clause' => $objectiveData['iso17025_clause'],
                'iso17025_requirement' => $objectiveData['iso17025_requirement'],
                'remarks' => 'Initial seeded data'
            ]);
        }
    }
}
