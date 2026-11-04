<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QualityPeriodService;

class InitializeFYRPeriods extends Command
{
    protected $signature = 'quality:init-fyr';
    protected $description = 'Initialize FYR periods for the quality management system';

    public function handle()
    {
        $this->info('🔄 Initializing FYR periods...');

        try {
            QualityPeriodService::initializeFYRs();
            $this->info('✅ FYR periods initialized successfully!');

            $current = QualityPeriodService::getCurrentFYR();
            if ($current) {
                $this->info("📅 Current FYR: {$current->fyr}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
