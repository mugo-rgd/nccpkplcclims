<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TestResult;
use App\Services\TrialDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixTrialData extends Command
{
    protected $signature = 'fix:trial-data {--dry-run : Perform a dry run without making changes}';
    protected $description = 'Fix trial_data JSON format by converting string values to numbers';

    public function handle()
    {
        $this->info('========================================');
        $this->info('  TRIAL DATA FIX TOOL');
        $this->info('========================================');
        $this->newLine();

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
            $this->warn('   Use this to preview what will be changed');
            $this->newLine();
        }

        // Get all records with trial_data using DB facade to avoid casting
        $testResults = DB::table('test_results')
            ->whereNotNull('trial_data')
            ->get();

        $total = $testResults->count();

        if ($total === 0) {
            $this->info('✅ No records found with trial_data. Nothing to fix.');
            return 0;
        }

        $this->info("📊 Found {$total} records with trial_data");
        $this->newLine();

        $processed = 0;
        $converted = 0;
        $alreadyCorrect = 0;
        $errors = [];

        // Create progress bar
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($testResults as $result) {
            try {
                // Decode the JSON string from the database
                $trialData = json_decode($result->trial_data, true);

                // If it's still a string, try to decode it again
                if (is_string($trialData)) {
                    $trialData = json_decode($trialData, true);
                }

                if (!is_array($trialData)) {
                    $errors[] = "ID {$result->id}: Invalid JSON format - data is not an array";
                    $bar->advance();
                    continue;
                }

                // Check if data needs conversion
                $needsConversion = false;
                $convertedData = [];

                foreach ($trialData as $key => $value) {
                    if (is_string($value) && is_numeric($value)) {
                        $needsConversion = true;
                        $convertedData[$key] = floatval($value);
                    } elseif (is_numeric($value)) {
                        $convertedData[$key] = floatval($value);
                    } else {
                        // Keep as is for non-numeric strings
                        $convertedData[$key] = $value;
                    }
                }

                if ($needsConversion) {
                    $originalJson = json_encode($trialData);
                    $newJson = json_encode($convertedData);

                    if (!$dryRun) {
                        DB::table('test_results')
                            ->where('id', $result->id)
                            ->update(['trial_data' => $newJson]);
                    }

                    $converted++;

                    if ($dryRun) {
                        $this->line("\n📝 ID {$result->id}: Would convert");
                        $this->line("   From: " . json_encode($trialData));
                        $this->line("   To:   " . json_encode($convertedData));
                        $this->line("");
                    }
                } else {
                    $alreadyCorrect++;
                }

                $processed++;

            } catch (\Exception $e) {
                $errors[] = "ID {$result->id}: " . $e->getMessage();
                Log::error("Failed to fix trial_data for ID {$result->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('========================================');
        $this->info('  SUMMARY');
        $this->info('========================================');
        $this->info("📊 Total records processed: {$processed}");
        $this->info("✅ Already correct: {$alreadyCorrect}");

        if (!$dryRun) {
            $this->info("🔄 Records converted: {$converted}");
        } else {
            $this->info("🔄 Records that would be converted: {$converted}");
        }

        if (!empty($errors)) {
            $this->error("❌ Errors encountered: " . count($errors));
            foreach ($errors as $error) {
                $this->error("   - {$error}");
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  This was a DRY RUN. No changes were made.');
            $this->warn('   To apply changes, run: php artisan fix:trial-data');
        } else {
            $this->info('✅ Fix completed successfully!');
        }

        $this->newLine();

        return 0;
    }
}
