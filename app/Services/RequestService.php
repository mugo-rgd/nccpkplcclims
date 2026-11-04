<?php

namespace App\Services;

use App\Models\CustomerRequest;
use App\Models\Quote;
use App\Models\JobCard;
use App\Models\Certificate;
use App\Models\DeliveryRecord;
use Illuminate\Support\Facades\DB;

class RequestService
{
    public function __construct(
        private SequenceService $sequenceService
    ) {}

    // ... existing methods ...

    /**
     * Receive equipment at laboratory
     */
    public function receiveEquipmentAtLab(CustomerRequest $request, array $data): void
    {
        DB::transaction(function () use ($request, $data) {
            $request->update([
                'received_at_lab' => now(),
                'received_by' => $data['received_by'] ?? auth()->id(),
                'equipment_condition_notes' => $data['condition_notes'] ?? null,
                'is_equipment_damaged' => $data['is_damaged'] ?? false,
            ]);

            // Create delivery record
            DeliveryRecord::create([
                'customer_request_id' => $request->id,
                'delivery_number' => $this->sequenceService->getNextNumber('delivery'),
                'type' => 'delivery',
                'method' => 'pickup',
                'from_address' => $request->customer->address,
                'to_address' => 'Meter Central Laboratory',
                'contact_person' => $data['contact_person'] ?? $request->customer->contact_person,
                'contact_phone' => $data['contact_phone'] ?? $request->customer->phone,
                'received_at' => now(),
                'items' => [
                    'equipment_type' => $request->equipment_type,
                    'manufacturer' => $request->manufacturer,
                    'model' => $request->model,
                    'serial_number' => $request->serial_number,
                ],
                'condition_notes' => $data['condition_notes'] ?? null,
                'status' => 'delivered',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Schedule on-site visit
     */
    public function scheduleSiteVisit(CustomerRequest $request, array $data): void
    {
        DB::transaction(function () use ($request, $data) {
            $request->update([
                'site_visit_date' => $data['site_visit_date'],
                'site_address' => $data['site_address'] ?? $request->customer->address,
                'site_contact_person' => $data['site_contact_person'] ?? $request->customer->contact_person,
                'site_contact_phone' => $data['site_contact_phone'] ?? $request->customer->phone,
                'site_instructions' => $data['site_instructions'] ?? null,
                'site_access_requirements' => $data['site_access_requirements'] ?? null,
            ]);

            // Create delivery record for site visit
            DeliveryRecord::create([
                'customer_request_id' => $request->id,
                'delivery_number' => $this->sequenceService->getNextNumber('delivery'),
                'type' => 'delivery',
                'method' => 'site_visit',
                'to_address' => $data['site_address'] ?? $request->customer->address,
                'contact_person' => $data['site_contact_person'] ?? $request->customer->contact_person,
                'contact_phone' => $data['site_contact_phone'] ?? $request->customer->phone,
                'scheduled_at' => $data['site_visit_date'],
                'items' => [
                    'equipment_type' => $request->equipment_type,
                    'manufacturer' => $request->manufacturer,
                    'model' => $request->model,
                    'serial_number' => $request->serial_number,
                ],
                'handling_instructions' => $data['site_instructions'] ?? null,
                'status' => 'scheduled',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Collect equipment from laboratory
     */
    public function collectEquipment(CustomerRequest $request, array $data): void
    {
        DB::transaction(function () use ($request, $data) {
            $request->update([
                'collected_at' => now(),
                'collected_by' => $data['collected_by'] ?? $request->customer->contact_person,
                'collection_notes' => $data['collection_notes'] ?? null,
            ]);

            // Create delivery record for collection
            DeliveryRecord::create([
                'customer_request_id' => $request->id,
                'job_card_id' => $request->jobCard?->id,
                'delivery_number' => $this->sequenceService->getNextNumber('delivery'),
                'type' => 'collection',
                'method' => 'pickup',
                'from_address' => 'Meter Central Laboratory',
                'to_address' => $request->customer->address,
                'contact_person' => $data['collected_by'] ?? $request->customer->contact_person,
                'contact_phone' => $data['contact_phone'] ?? $request->customer->phone,
                'collected_at' => now(),
                'items' => [
                    'equipment_type' => $request->equipment_type,
                    'manufacturer' => $request->manufacturer,
                    'model' => $request->model,
                    'serial_number' => $request->serial_number,
                    'certificate_number' => $request->certificate?->certificate_number,
                ],
                'condition_notes' => $data['collection_notes'] ?? null,
                'status' => 'collected',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Complete on-site calibration
     */
    public function completeOnSiteCalibration(JobCard $jobCard, array $data): void
    {
        DB::transaction(function () use ($jobCard, $data) {
            $jobCard->update([
                'status' => 'completed',
                'work_done' => $data['work_done'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'completed_at' => now(),
                'checked_by' => $data['checked_by'] ?? auth()->id(),
                'verified_at' => now(),
            ]);

            // Update customer request
            $jobCard->customerRequest->update([
                'status' => 'completed',
                'collected_at' => now(),
            ]);

            // Create delivery record for site visit completion
            DeliveryRecord::create([
                'customer_request_id' => $jobCard->customer_request_id,
                'job_card_id' => $jobCard->id,
                'delivery_number' => $this->sequenceService->getNextNumber('delivery'),
                'type' => 'collection',
                'method' => 'site_visit',
                'from_address' => $jobCard->customerRequest->site_address,
                'to_address' => 'Meter Central Laboratory',
                'contact_person' => $jobCard->customerRequest->site_contact_person,
                'contact_phone' => $jobCard->customerRequest->site_contact_phone,
                'collected_at' => now(),
                'items' => [
                    'equipment_type' => $jobCard->customerRequest->equipment_type,
                    'manufacturer' => $jobCard->customerRequest->manufacturer,
                    'model' => $jobCard->customerRequest->model,
                    'serial_number' => $jobCard->customerRequest->serial_number,
                ],
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);
        });
    }
}
