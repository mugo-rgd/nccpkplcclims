<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\Customer;
use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use App\Models\PublicQuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;


class PublicQuoteRequestController extends Controller
{
    /**
     * List public quotation requests.
     */
     public function index(Request $request): View
    {
        $query = PublicQuoteRequest::query()
            ->with([
                'items.equipmentType',          // ← CHANGED (was 'equipmentType')
                'items.serviceType',            // ← CHANGED
                'calibration',
                'reviewedBy',
            ])
            ->withCount('items');               // ← CHANGED

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search): void {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        $quoteRequests = $query
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.quote-requests.index', compact('quoteRequests'));
    }

    public function show(PublicQuoteRequest $quoteRequest): View
{
    $quoteRequest->load([
        'items.equipmentType',
        'items.serviceType',
        'calibration',
        'reviewedBy',
    ]);

    $customers = Customer::query()
        ->orderBy('company_name')
        ->select(['id', 'company_name', 'email', 'phone'])
        ->get();

    $equipmentTypes = KplcEquipmentType::query()
        ->orderBy('name')
        ->select(['id', 'name', 'rate'])
        ->get();

    $serviceTypes = KplcServiceType::query()
        ->orderBy('serv_name')
        ->select(['id', 'serv_name', 'serv_code', 'serv_method'])
        ->get();

    return view('admin.quote-requests.show', compact(
        'quoteRequest',
        'customers',
        'equipmentTypes',
        'serviceTypes'
    ));
}


    /**
     * Convert the public enquiry into an official
     * calibration request.
     */
  public function convert(Request $request, PublicQuoteRequest $quoteRequest): RedirectResponse
{
    $validated = $request->validate([
        'customer_id'    => ['nullable', 'integer', 'exists:customers,id'],
        'company_name'   => ['nullable', 'string', 'max:255'],
        'contact_person' => ['required', 'string', 'max:255'],
        'email'          => ['required', 'email', 'max:255'],
        'phone'          => ['required', 'string', 'max:50'],
        'notes'          => ['nullable', 'string', 'max:3000'],

        'items'                          => ['required', 'array', 'min:1'],
        'items.*.equipment_type_id'      => ['required', 'integer', 'exists:kplc_equipment_types,id'],
        'items.*.equipment_name'         => ['required', 'string', 'max:255'],
        'items.*.serial_number'          => ['required', 'string', 'max:255'],
        'items.*.manufacturer'           => ['nullable', 'string', 'max:255'],
        'items.*.model'                  => ['nullable', 'string', 'max:255'],
        'items.*.ratings'                => ['nullable', 'string', 'max:255'],
        'items.*.equipment_condition'    => ['required', 'in:good,fair,damaged'],
        'items.*.service_types'          => ['nullable', 'array'],
        'items.*.service_types.*'        => ['integer', 'exists:kplc_service_types,id'],
        'items.*.notes'                  => ['nullable', 'string', 'max:1000'],
    ]);

    if ($quoteRequest->status === 'converted' && $quoteRequest->calibration_id) {
        return redirect()
            ->route('calibrations.show', $quoteRequest->calibration_id)
            ->with('info', 'This request has already been converted.');
    }

    $first = $validated['items'][0];

    $calibration = DB::transaction(function () use ($validated, $quoteRequest, $first) {

        if (!empty($validated['customer_id'])) {
            $customer = Customer::findOrFail($validated['customer_id']);
        } else {
            $customer = Customer::firstOrCreate(
                ['email' => strtolower(trim($validated['email']))],
                [
                    'company_name'   => $validated['company_name'] ?? null,
                    'contact_person' => $validated['contact_person'],
                    'phone'          => $validated['phone'],
                    'address'          => $validated['phone'],
                ]
            );
        }

        $calibration = Calibration::create([
            'customer_id'         => $customer->id,
            'quote_request_id'    => $quoteRequest->id,
            'created_by'          => auth()->id(),
            'status'              => 'pending',
            'notes'               => $validated['notes'] ?? null,

            // Legacy header columns — keep the first item so existing
            // reports that read the header still work.
            'equipment_type_id'   => $first['equipment_type_id'],
            'equipment_name'      => $first['equipment_name'],
            'serial_number'       => $first['serial_number'],
            'manufacturer'        => $first['manufacturer'] ?? null,
            'model'               => $first['model'] ?? null,
            'ratings'             => $first['ratings'] ?? null,
            'equipment_condition' => $first['equipment_condition'],
            'service_types'       => $first['service_types'] ?? [],
        ]);

        foreach ($validated['items'] as $i => $row) {
            $calibration->items()->create([
                'quote_request_item_id' => $row['quote_request_item_id'] ?? null,
                'equipment_type_id'     => $row['equipment_type_id'],
                'equipment_name'        => $row['equipment_name'],
                'serial_number'         => $row['serial_number'],
                'manufacturer'          => $row['manufacturer'] ?? null,
                'model'                 => $row['model'] ?? null,
                'ratings'               => $row['ratings'] ?? null,   // if you added `ratings` to calibration_items
                'equipment_condition'   => $row['equipment_condition'],
                'service_types'         => $row['service_types'] ?? [],
                'notes'                 => $row['notes'] ?? null,
                'status'                => 'pending',
                'position'              => $i,
            ]);
        }

        $quoteRequest->update([
            'status'         => 'converted',
            'calibration_id' => $calibration->id,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'converted_at'   => now(),
        ]);

        return $calibration;
    });

    return redirect()
        ->route('calibrations.show', $calibration)
        ->with('success', 'Calibration request #' . $calibration->id
            . ' created from ' . count($validated['items']) . ' item(s).');
}

}
