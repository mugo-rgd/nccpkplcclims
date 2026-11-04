<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRecord;
use App\Models\CustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryRecordController extends Controller
{
    /**
     * Display a listing of delivery records.
     */
    public function index(Request $request)
    {
        $query = DeliveryRecord::with(['customerRequest', 'jobCard', 'createdBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('delivery_number', 'LIKE', "%{$search}%")
                  ->orWhere('contact_person', 'LIKE', "%{$search}%")
                  ->orWhere('contact_phone', 'LIKE', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(15);
        $types = ['delivery', 'collection'];
        $statuses = ['pending', 'scheduled', 'dispatched', 'delivered', 'collected', 'completed', 'cancelled'];

        return view('admin.deliveries.index', compact('records', 'types', 'statuses'));
    }

    /**
     * Show the form for creating a new delivery record.
     */
    public function create()
    {
        $requests = CustomerRequest::whereIn('status', ['paid', 'processing', 'completed'])
            ->pluck('request_number', 'id');

        return view('admin.deliveries.create', compact('requests'));
    }

    /**
     * Store a newly created delivery record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_request_id' => 'required|exists:customer_requests,id',
            'type' => 'required|in:delivery,collection',
            'method' => 'required|in:pickup,courier,site_visit',
            'from_address' => 'nullable|string|max:500',
            'to_address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'scheduled_at' => 'nullable|date|after:now',
            'items' => 'nullable|array',
            'condition_notes' => 'nullable|string',
            'handling_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['delivery_number'] = $this->generateDeliveryNumber();
        $validated['status'] = 'pending';
        $validated['created_by'] = auth()->id();

        $record = DeliveryRecord::create($validated);

        return redirect()->route('deliveries.show', $record)
            ->with('success', 'Delivery record created successfully!');
    }

    /**
     * Display the specified delivery record.
     */
    public function show(DeliveryRecord $delivery)
    {
        $delivery->load(['customerRequest', 'jobCard', 'createdBy']);
        return view('admin.deliveries.show', compact('delivery'));
    }

    /**
     * Show the form for editing the specified delivery record.
     */
    public function edit(DeliveryRecord $delivery)
    {
        $requests = CustomerRequest::whereIn('status', ['paid', 'processing', 'completed'])
            ->pluck('request_number', 'id');

        return view('admin.deliveries.edit', compact('delivery', 'requests'));
    }

    /**
     * Update the specified delivery record.
     */
    public function update(Request $request, DeliveryRecord $delivery)
    {
        $validated = $request->validate([
            'type' => 'required|in:delivery,collection',
            'method' => 'required|in:pickup,courier,site_visit',
            'from_address' => 'nullable|string|max:500',
            'to_address' => 'nullable|string|max:500',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'scheduled_at' => 'nullable|date',
            'dispatched_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'collected_at' => 'nullable|date',
            'items' => 'nullable|array',
            'condition_notes' => 'nullable|string',
            'handling_instructions' => 'nullable|string',
            'status' => 'required|in:pending,scheduled,dispatched,delivered,collected,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $delivery->update($validated);

        return redirect()->route('deliveries.show', $delivery)
            ->with('success', 'Delivery record updated successfully!');
    }

    /**
     * Update delivery status to dispatched.
     */
    public function dispatch(DeliveryRecord $delivery)
    {
        $delivery->update([
            'status' => 'dispatched',
            'dispatched_at' => now(),
        ]);

        return redirect()->route('deliveries.show', $delivery)
            ->with('success', 'Delivery marked as dispatched!');
    }

    /**
     * Update delivery status to delivered.
     */
    public function markDelivered(Request $request, DeliveryRecord $delivery)
    {
        $request->validate([
            'receiver_name' => 'nullable|string|max:255',
            'receiver_signature' => 'nullable|string',
        ]);

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'receiver_name' => $request->receiver_name,
            'receiver_signature_path' => $request->receiver_signature,
        ]);

        // Update customer request if this is a delivery
        if ($delivery->type === 'delivery') {
            $delivery->customerRequest->update([
                'received_at_lab' => now(),
                'received_by' => $request->receiver_name ?? auth()->user()->name,
            ]);
        }

        return redirect()->route('deliveries.show', $delivery)
            ->with('success', 'Delivery marked as delivered!');
    }

    /**
     * Update delivery status to collected.
     */
    public function markCollected(Request $request, DeliveryRecord $delivery)
    {
        $request->validate([
            'sender_name' => 'nullable|string|max:255',
            'sender_signature' => 'nullable|string',
        ]);

        $delivery->update([
            'status' => 'collected',
            'collected_at' => now(),
            'sender_name' => $request->sender_name,
            'sender_signature_path' => $request->sender_signature,
        ]);

        // Update customer request if this is a collection
        if ($delivery->type === 'collection') {
            $delivery->customerRequest->update([
                'collected_at' => now(),
                'collected_by' => $request->sender_name ?? auth()->user()->name,
            ]);
        }

        return redirect()->route('deliveries.show', $delivery)
            ->with('success', 'Collection marked as completed!');
    }

    /**
     * Cancel the delivery record.
     */
    public function cancel(DeliveryRecord $delivery)
    {
        $delivery->update(['status' => 'cancelled']);

        return redirect()->route('deliveries.show', $delivery)
            ->with('warning', 'Delivery record cancelled!');
    }

    /**
     * Remove the specified delivery record.
     */
    public function destroy(DeliveryRecord $delivery)
    {
        try {
            $delivery->delete();
            return redirect()->route('deliveries.index')
                ->with('success', 'Delivery record deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete delivery record: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique delivery number.
     */
    private function generateDeliveryNumber(): string
    {
        $prefix = 'DEL';
        $year = date('Y');
        $month = date('m');
        $last = DeliveryRecord::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return $prefix . $year . $month . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
