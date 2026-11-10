<?php

namespace App\Http\Controllers;

use App\Models\PublicQuoteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicQuoteRequestController extends Controller
{
    /**
     * Store a public calibration quotation enquiry
     * with one or more equipment/service line items.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:3000'],

            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.equipment_type_id' => [
                'required',
                'integer',
                'exists:kplc_equipment_types,id',
            ],
            'items.*.service_type_id' => [
                'required',
                'integer',
                'exists:kplc_service_types,id', // adjust table name
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.equipment_name' => ['nullable', 'string', 'max:255'],
'items.*.serial_number'  => ['nullable', 'string', 'max:255'],
'items.*.manufacturer'   => ['nullable', 'string', 'max:255'],
'items.*.model'          => ['nullable', 'string', 'max:255'],
'items.*.rating'         => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $quoteRequest = DB::transaction(function () use ($validated) {
                $referenceNumber = $this->generateReferenceNumber();

                $quoteRequest = PublicQuoteRequest::query()->create([
                    'reference_number' => $referenceNumber,
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'phone' => trim($validated['phone']),
                    'company' => !empty($validated['company'])
                        ? trim($validated['company'])
                        : null,
                    'requirements' => !empty($validated['requirements'])
                        ? trim($validated['requirements'])
                        : null,
                    'status' => 'pending',
                ]);

              $items = collect($validated['items'])->map(function ($item) {
    return [
        'equipment_type_id' => (int) $item['equipment_type_id'],
        'service_type_id'   => (int) $item['service_type_id'],
        'quantity'          => (int) $item['quantity'],

        'equipment_name' => $item['equipment_name'] ?? null,
        'serial_number'  => $item['serial_number'] ?? null,
        'manufacturer'   => $item['manufacturer'] ?? null,
        'model'          => $item['model'] ?? null,
        'rating'         => $item['rating'] ?? null,
    ];
})->all();

                $quoteRequest->items()->createMany($items);

                return $quoteRequest->load('items');
            });
        } catch (\Throwable $exception) {
            Log::error('Public calibration quote request failed.', [
                'email' => $validated['email'] ?? null,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not submit your quotation request. Please try again.',
            ], 500);
        }

        Log::info('Public calibration quote request submitted.', [
            'quote_request_id' => $quoteRequest->id,
            'reference_number' => $quoteRequest->reference_number,
            'items_count' => $quoteRequest->items->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your calibration quotation request has been submitted successfully. '
                . 'Kenya Power Meter Central Laboratory will review the request and send you an official quotation.',
            'reference_number' => $quoteRequest->reference_number,
        ]);
    }

    /**
     * Generate a collision-safe reference number.
     * MCL-QR-YYYYMMDD-XXXXXX
     */
    private function generateReferenceNumber(): string
    {
        // Up to 5 attempts in the (very unlikely) event of a unique-index clash.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = 'MCL-QR-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(6));

            $exists = PublicQuoteRequest::query()
                ->where('reference_number', $candidate)
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        // Fallback: append microseconds so we cannot collide.
        return 'MCL-QR-'
            . now()->format('YmdHisv')
            . '-'
            . strtoupper(Str::random(4));
    }
}
