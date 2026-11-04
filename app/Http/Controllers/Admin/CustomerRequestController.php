<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerRequest;
use App\Models\Customer;
use App\Services\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerRequestController extends Controller
{
    public function __construct(
        private RequestService $requestService
    ) {}

    public function index(Request $request)
    {
        $query = CustomerRequest::with(['customer', 'quote', 'jobCard']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function($cq) use ($search) {
                      $cq->where('company_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        $statuses = ['draft', 'pending_quote', 'quoted', 'approved', 'paid', 'processing', 'completed', 'rejected'];

        return view('admin.requests.index', compact('requests', 'statuses'));
    }

    public function create()
    {
        $customers = Customer::active()->pluck('company_name', 'id');
        $serviceTypes = ['calibration', 'testing', 'repair'];
        $methods = [
            'IEC 62052-11' => 'IEC 62052-11: Electricity metering equipment (AC)',
            'IEC 62058-31' => 'IEC 62058-31: Electricity metering equipment (AC) - Acceptance inspection',
            'IEC 60898-2' => 'IEC 60898-2: Circuit-breakers for overcurrent protection',
            'IEC 60044-1' => 'IEC 60044-1: Instrument Transformers - Current Transformers',
            'IEC 61869-1&2' => 'IEC 61869-1&2: Instrument transformers',
            'ANSI/NCSL Z 540.3' => 'ANSI/NCSL Z 540.3: Calibration of measuring and test equipment',
        ];

        return view('admin.requests.create', compact('customers', 'serviceTypes', 'methods'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'equipment_type' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'ratings' => 'nullable|string|max:255',
            'service_type' => 'required|in:calibration,testing,repair',
            'method' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requires_calibration_validity' => 'boolean',
            'validity_period_days' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $request = $this->requestService->createRequest($validated);

        return redirect()->route('requests.show', $request)
            ->with('success', 'Request created successfully! Request Number: ' . $request->request_number);
    }

    public function show(CustomerRequest $request)
    {
        $request->load(['customer', 'quote', 'jobCard', 'payments', 'certificate']);
        return view('admin.requests.show', compact('request'));
    }

    public function edit(CustomerRequest $request)
    {
        $customers = Customer::active()->pluck('company_name', 'id');
        $serviceTypes = ['calibration', 'testing', 'repair'];
        $statuses = ['draft', 'pending_quote', 'quoted', 'approved', 'paid', 'processing', 'completed', 'rejected'];

        return view('admin.requests.edit', compact('request', 'customers', 'serviceTypes', 'statuses'));
    }

    public function update(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'equipment_type' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'ratings' => 'nullable|string|max:255',
            'service_type' => 'required|in:calibration,testing,repair',
            'method' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,pending_quote,quoted,approved,paid,processing,completed,rejected',
            'notes' => 'nullable|string',
        ]);

        $customerRequest->update($validated);

        return redirect()->route('requests.show', $customerRequest)
            ->with('success', 'Request updated successfully!');
    }

    public function generateQuote(CustomerRequest $request)
    {
        return view('admin.requests.generate-quote', compact('request'));
    }

    public function storeQuote(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'terms_conditions' => 'nullable|string',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $quote = $this->requestService->generateQuote($customerRequest, $validated);

        return redirect()->route('requests.show', $customerRequest)
            ->with('success', 'Quote generated! Quote Number: ' . $quote->quote_number);
    }

    public function approveQuote(CustomerRequest $request)
    {
        if (!$request->quote) {
            return back()->with('error', 'No quote found for this request.');
        }

        $this->requestService->approveQuote($request->quote);

        return redirect()->route('requests.show', $request)
            ->with('success', 'Quote approved! Customer can now make payment.');
    }

    public function createJobCard(CustomerRequest $request)
    {
        $technicians = \App\Models\User::role('lab_technician')
            ->where('is_active', true)
            ->pluck('name', 'id');

        return view('admin.requests.create-jobcard', compact('request', 'technicians'));
    }

    public function storeJobCard(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'allocated_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $jobCard = $this->requestService->createJobCard($customerRequest, $validated);

        return redirect()->route('jobcards.show', $jobCard)
            ->with('success', 'Job Card created! Job Card Number: ' . $jobCard->job_card_number);
    }

    /**
     * Show receive equipment form
     */
    public function receiveEquipment(CustomerRequest $request)
    {
        return view('admin.requests.receive-equipment', compact('request'));
    }

    /**
     * Process equipment receiving
     */
    public function storeReceivedEquipment(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'condition_notes' => 'nullable|string',
            'is_damaged' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $this->requestService->receiveEquipmentAtLab($customerRequest, $validated);

        return redirect()->route('requests.show', $customerRequest)
            ->with('success', 'Equipment received successfully!');
    }

    /**
     * Show on-site scheduling form
     */
    public function scheduleSiteVisit(CustomerRequest $request)
    {
        return view('admin.requests.schedule-site-visit', compact('request'));
    }

    /**
     * Process site visit scheduling
     */
    public function storeSiteVisit(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'site_visit_date' => 'required|date|after:now',
            'site_address' => 'required|string|max:500',
            'site_contact_person' => 'required|string|max:255',
            'site_contact_phone' => 'required|string|max:20',
            'site_instructions' => 'nullable|string',
            'site_access_requirements' => 'nullable|string',
        ]);

        $this->requestService->scheduleSiteVisit($customerRequest, $validated);

        return redirect()->route('requests.show', $customerRequest)
            ->with('success', 'Site visit scheduled successfully!');
    }

    /**
     * Show collection form
     */
    public function collectEquipment(CustomerRequest $request)
    {
        return view('admin.requests.collect-equipment', compact('request'));
    }

    /**
     * Process equipment collection
     */
    public function storeCollection(Request $request, CustomerRequest $customerRequest)
    {
        $validated = $request->validate([
            'collected_by' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'collection_notes' => 'nullable|string',
        ]);

        $this->requestService->collectEquipment($customerRequest, $validated);

        return redirect()->route('requests.show', $customerRequest)
            ->with('success', 'Equipment collected successfully!');
    }
}
