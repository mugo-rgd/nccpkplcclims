<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QualityPeriodService;
use App\Models\QualityObjective;
use App\Models\QualityFyrPeriod;

class GenerateNextFYRTargets extends Command
{
    protected $signature = 'quality:generate-next-fyr
                            {--force : Force generation even if next FYR exists}
                            {--clean : Clean existing objectives for next FYR first}';
    protected $description = 'Generate quality objectives for the next FYR based on current targets';

    public function handle()
    {
        $this->info('🔄 Generating next FYR targets...');

        $currentFyr = QualityFyrPeriod::getCurrentFYR();
        if (!$currentFyr) {
            $this->error('❌ No current FYR found.');
            return 1;
        }

        $nextFyr = $currentFyr->getNextFYR();
        if (!$nextFyr) {
            $this->error('❌ No next FYR found. Please create FYR periods first.');
            return 1;
        }

        $this->info("📅 Current FYR: {$currentFyr->fyr}");
        $this->info("📅 Next FYR: {$nextFyr->fyr}");

        $existingCount = QualityObjective::where('target_fyr', $nextFyr->fyr)->count();

        if ($existingCount > 0) {
            if ($this->option('clean')) {
                $this->warn("🗑️ Cleaning existing objectives for {$nextFyr->fyr}...");
                QualityObjective::where('target_fyr', $nextFyr->fyr)->forceDelete();
                $this->info("✅ Removed {$existingCount} existing objectives.");
            } elseif (!$this->option('force')) {
                if (!$this->confirm("⚠️ Objectives for {$nextFyr->fyr} already exist. Overwrite?")) {
                    $this->info('❌ Operation cancelled.');
                    return 0;
                }

                // Delete existing objectives for next FYR
                QualityObjective::where('target_fyr', $nextFyr->fyr)->forceDelete();
                $this->info("🗑️ Removed existing objectives for {$nextFyr->fyr}");
            }
        }

        try {
            $result = QualityPeriodService::generateNextYearTargets();

            if ($result) {
                $this->newLine();
                $this->info('✅ Next FYR targets generated successfully!');
                $this->info("📊 FYR: {$nextFyr->fyr}");
                $this->info("📈 Total objectives: " . QualityObjective::where('target_fyr', $nextFyr->fyr)->count());
                $this->info("📝 Created: {$result['created']}, Updated: {$result['updated']}");
            } else {
                $this->error('❌ Failed to generate next FYR targets.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
