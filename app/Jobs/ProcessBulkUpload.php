<?php

namespace App\Jobs;

use App\Models\Calibration;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessBulkUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $customerId;
    protected $batchId;
    protected $userId;

    public function __construct($csvData, $customerId, $batchId, $userId)
    {
        $this->csvData = $csvData;
        $this->customerId = $customerId;
        $this->batchId = $batchId;
        $this->userId = $userId;
    }

    public function handle()
    {
        // Process the CSV data
        // This runs in the background
    }
}
