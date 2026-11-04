@extends('layouts.app')

@section('title', 'Generate Quotation')

@section('content')
@php
    $customer = $calibration->customer;
    $quotation = $calibration->quotation;
    $quotationGenerated = $quotation !== null;
    $customerEmail = $customer?->email;
    $customerVatRate = max(0, min(100, (float) app(\App\Services\CustomerVatService::class)->rate($customer)));
    $equipmentItems = $calibration->items ?? collect();
    $validUntil = $quotation?->valid_until;
    $validUntilDate = $validUntil instanceof \DateTimeInterface ? $validUntil->format('Y-m-d') : ($validUntil ? substr((string) $validUntil, 0, 10) : now()->addDays(30)->format('Y-m-d'));

    // Support stored service IDs or service codes without losing the equipment association.
    $serviceRecords = $serviceLookup->values();
    $serviceById = $serviceRecords->keyBy(fn ($service) => (string) $service->id);
    $serviceByCode = $serviceRecords->keyBy(fn ($service) => (string) $service->serv_code);
    $resolveServices = function ($equipment) use ($serviceById, $serviceByCode) {
        $raw = $equipment->service_types ?? [];
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        if (!is_array($raw)) {
            $raw = [];
        }
        return collect($raw)->map(fn ($value) => $serviceById->get((string) $value) ?? $serviceByCode->get((string) $value))->filter()->unique('id')->values();
    };

    // New quotations use one row for each requested equipment/service pair.

// Equipment prices are stored in kplc_equipment_types.
$equipmentTypeLookup = $equipmentTypes->keyBy('id');

$initialLines = collect();

foreach ($equipmentItems as $equipment) {

    $equipmentType = $equipmentTypeLookup->get(
        (int) $equipment->equipment_type_id
    );

    $unitPrice = $equipmentType
        ? (float) $equipmentType->rate
        : null;

    foreach ($resolveServices($equipment) as $service) {

        $initialLines->push([
            'description' =>
                ($equipment->equipment_name ?: 'Equipment')
                . ' ('
                . ($equipment->serial_number ?: 'No serial')
                . ') - '
                . $service->serv_code
                . ': '
                . $service->serv_name,

            'calibration_item_id' => $equipment->id,
            'service_type_id' => $service->id,
            'equipment_type_id' => $equipment->equipment_type_id,

            'quantity' => 1,
            'unit_price' => $unitPrice,
            'source' => 'requested',
        ]);
    }
}


    // Do not discard prices from an existing quotation when editing it.
    // This assumes the quotation has an items relationship with the displayed fields.
   if ($quotation) {
    $savedItems = collect($quotation->items_data ?? []);

    if ($savedItems->isNotEmpty()) {
        $initialLines = $savedItems
            ->reject(fn ($line) =>
                str_starts_with(
                    (string) ($line['type'] ?? ''),
                    'onsite_'
                )
            )
            ->map(fn ($line) => [
                'description' => $line['description'] ?? '',
                'calibration_item_id' => $line['calibration_item_id'] ?? null,
                'service_type_id' => $line['service_type_id'] ?? null,
                'quantity' => $line['quantity'] ?? 1,
                'unit_price' => $line['unit_price'] ?? 0,
                'source' => !empty($line['calibration_item_id'])
                    ? 'requested'
                    : 'custom',
            ])
            ->values();
    }
}
    $initialLines = collect(old('items', $initialLines->all()))->values();
@endphp

<div class="container-fluid quotation-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h1 class="h3 mb-0">{{ $quotationGenerated ? 'Update Quotation' : 'Generate Quotation' }}</h1>
        <a href="{{ route('calibrations.show', $calibration->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Calibration
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please correct these errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary"><i class="fas fa-file-invoice me-2"></i>Quotation for Calibration #{{ $calibration->id }}</h5>
        </div>
        <div class="card-body">
            <form id="quotationForm" method="POST" action="{{ route('calibrations.store-quotation', $calibration->id) }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-header bg-light"><h6 class="mb-0 text-primary"><i class="fas fa-user me-2"></i>Customer Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label fw-semibold">Company Name</label><input class="form-control" value="{{ $customer?->company_name ?? $customer?->name ?? 'N/A' }}" readonly></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Contact Person</label><input class="form-control" value="{{ $customer?->contact_person ?? $customer?->name ?? 'N/A' }}" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input class="form-control" value="{{ $customer?->phone ?? 'N/A' }}" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input class="form-control" value="{{ $customerEmail ?? 'N/A' }}" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Customer Type</label><input class="form-control" value="{{ $calibration->is_external ? 'External' : 'Internal (KPLC)' }}" readonly></div>
                            <div class="col-12"><label class="form-label fw-semibold">Address</label><input class="form-control" value="{{ $customer?->address ?? 'N/A' }}" readonly></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 border-warning">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 text-primary"><i class="fas fa-tools me-2"></i>Customer Equipment</h6>
                        <span class="badge bg-primary">{{ $equipmentItems->count() }} item(s)</span>
                    </div>
                    <div class="card-body">
                        @forelse($equipmentItems as $index => $equipment)
                            @php $requestedServices = $resolveServices($equipment); @endphp
                            <div class="equipment-card border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <h6 class="fw-bold mb-0">{{ $index + 1 }}. {{ $equipment->equipment_name ?: 'Unnamed equipment' }}</h6>
                                    <span class="badge bg-secondary">Serial: {{ $equipment->serial_number ?: 'N/A' }}</span>
                                </div>
                                <div class="row g-2 mb-2 small">
                                    <div class="col-md-4"><span class="text-muted">Manufacturer:</span> {{ $equipment->manufacturer ?: 'N/A' }}</div>
                                    <div class="col-md-4"><span class="text-muted">Model:</span> {{ $equipment->model ?: 'N/A' }}</div>
                                    <div class="col-md-4"><span class="text-muted">Condition:</span> {{ $equipment->equipment_condition ?: 'N/A' }}</div>
                                </div>
                                <div class="small text-muted mb-1">Requested service types</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($requestedServices as $service)
                                        <span class="badge bg-info text-dark" title="{{ $service->serv_name }}">{{ $service->serv_code }} — {{ $service->serv_name }}</span>
                                    @empty
                                        <span class="text-warning">No matching service types found. Verify stored IDs/codes.</span>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning mb-0">No customer equipment was recorded for this calibration.</div>
                        @endforelse
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white"><h6 class="mb-0 text-primary"><i class="fas fa-list me-2"></i>Quotation Items</h6></div>
                    <div class="card-body">
                        <p class="text-muted small">Each requested equipment/service combination is listed separately. Enter the approved unit price for each line.</p>
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-10">
                                <label class="form-label fw-semibold" for="equipmentTypeSelector">Add an additional equipment-type charge</label>
                                <select id="equipmentTypeSelector" class="form-select">
                                    <option value="">-- Select Equipment Type --</option>
                                    @foreach($equipmentTypes as $type)
                                        <option value="{{ $type->id }}" data-name="{{ $type->name }}" data-rate="{{ $type->rate }}">{{ $type->name }} — KSh {{ number_format((float) $type->rate, 2) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2"><button type="button" id="addEquipmentItem" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button></div>
                        </div>

                        <div id="items-container">
                            @foreach($initialLines as $index => $line)
                                <div class="item-row border rounded p-3 mb-3" data-source="{{ $line['source'] ?? 'custom' }}">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-lg-5">
                                            <label class="form-label small fw-semibold">Description</label>
                                            <input type="text" name="items[{{ $index }}][description]" class="form-control description" value="{{ $line['description'] ?? '' }}" required>
                                            @if(!empty($line['calibration_item_id']))
                                                <input type="hidden" name="items[{{ $index }}][calibration_item_id]" value="{{ $line['calibration_item_id'] }}">
                                            @endif
                                            @if(!empty($line['service_type_id']))
                                                <input type="hidden" name="items[{{ $index }}][service_type_id]" value="{{ $line['service_type_id'] }}">
                                            @endif
                                        </div>
                                        <div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Quantity</label><input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity" value="{{ $line['quantity'] ?? 1 }}" min="0.01" step="0.01" required></div>
                                        <div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Unit Price (KSh)</label><input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price" value="{{ $line['unit_price'] ?? '' }}" min="0" step="0.01" required></div>
                                        <div class="col-9 col-lg-2"><label class="form-label small fw-semibold">Line Total</label><input type="text" class="form-control item-total" value="0.00" readonly></div>
                                        <div class="col-3 col-lg-1"><button type="button" class="btn btn-outline-danger w-100 remove-item" aria-label="Remove quotation line" title="Remove quotation line"><i class="fas fa-times"></i></button></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-item" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Add Custom Item</button>
                        <div id="itemsError" class="text-danger small mt-2 d-none" role="alert"></div>
                    </div>
                </div>

                @if($calibration->calibration_location === 'on_site')
                    <div class="card border-warning mb-4" id="onsite-costs-section">
                        <div class="card-header bg-warning text-dark"><h6 class="mb-0"><i class="fas fa-truck me-2"></i>On-Site Costs</h6></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label fw-semibold">Number of Staff</label><input type="number" name="number_of_staff" id="numberOfStaff" class="form-control" value="{{ old('number_of_staff', $calibration->number_of_staff ?? 1) }}" min="1" step="1"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Travel Days</label><input type="number" name="travel_days" id="travelDays" class="form-control" value="{{ old('travel_days', $calibration->travel_days ?? 1) }}" min="1" step="1"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Site Distance (KM)</label><input type="number" name="site_distance_km" id="siteDistance" class="form-control" value="{{ old('site_distance_km', $calibration->site_distance_km ?? 0) }}" min="0" step="0.01"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Per Diem per Staff (KSh)</label><input type="number" name="per_diem_rate" id="perDiemRate" class="form-control" value="{{ old('per_diem_rate', $quotation->per_diem_rate ?? 0) }}" min="0" step="0.01"><small class="text-muted">Per day per staff; enter approved amount</small></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Transport Cost (KSh)</label><input type="number" name="transport_cost" id="transportCost" class="form-control" value="{{ old('transport_cost', $quotation->transport_cost ?? $calibration->transport_cost ?? 0) }}" min="0" step="0.01"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Other Costs (KSh)</label><input type="number" name="other_costs" id="otherCosts" class="form-control" value="{{ old('other_costs', $quotation->other_costs ?? $calibration->other_costs ?? 0) }}" min="0" step="0.01"></div>
                                <div class="col-12"><label class="form-label fw-semibold">Other Costs Description</label><input type="text" name="other_costs_description" class="form-control" value="{{ old('other_costs_description', $quotation->other_costs_description ?? $calibration->other_costs_description ?? '') }}"></div>
                            </div>
                            <div class="alert alert-info mt-3 mb-0 small">
                                <strong>On-Site Cost Summary:</strong> Per Diem <span id="perDiemTotal">KSh 0.00</span> · Transport <span id="transportTotal">KSh 0.00</span> · Other <span id="otherTotal">KSh 0.00</span> · <strong>Total <span id="onsiteTotal">KSh 0.00</span></strong>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card border-success mb-4">
                    <div class="card-header bg-success text-white"><h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Quotation Summary</h6></div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3"><label class="form-label fw-semibold">Subtotal</label><input id="subtotal" class="form-control" value="0.00" readonly></div>
                            <div class="col-md-3"><label class="form-label fw-semibold">Discount (%)</label><input type="number" name="discount_percentage" id="discountPercentage" class="form-control" value="{{ old('discount_percentage', $quotation->discount_percentage ?? 0) }}" min="0" max="100" step="0.01"></div>
                            <div class="col-md-3"><label class="form-label fw-semibold">Tax Rate (%)</label><input type="number" name="tax_rate" id="taxRate" class="form-control" value="{{ number_format($customerVatRate, 2, '.', '') }}" readonly><small class="text-muted">From verified customer VAT settings</small></div>
                            <div class="col-md-3"><label class="form-label fw-semibold">On-Site Costs</label><input id="onsiteSummary" class="form-control" value="0.00" readonly></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label fw-semibold">Tax Amount</label><input id="taxAmount" class="form-control" value="0.00" readonly></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Discount Amount</label><input id="discountAmountDisplay" class="form-control" value="0.00" readonly><input type="hidden" name="discount_amount" id="discountAmountHidden" value="0.00"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Total Amount (KSh)</label><input id="totalAmount" class="form-control fw-bold" value="0.00" readonly><input type="hidden" name="total_amount" id="totalAmountHidden" value="0.00"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><label class="form-label fw-semibold">Valid Until</label><input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $validUntilDate) }}" required></div>
                            <div class="col-md-6"><label class="form-label fw-semibold">Terms &amp; Conditions</label><textarea name="terms" class="form-control" rows="2">{{ old('terms', $quotation->terms ?? 'Payment due within 30 days.') }}</textarea></div>
                            <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $quotation->notes ?? '') }}</textarea></div>
                        </div>
                        <p class="text-muted small mb-0">Preview only: the controller must recalculate all totals and the applicable VAT rate before saving. Confirm Finance's VAT treatment of on-site charges.</p>
                    </div>
                </div>

                <div class="d-flex justify-content-end flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-lg" id="generateQuotationButton"><i class="fas fa-file-invoice me-1"></i>{{ $quotationGenerated ? 'Update Quotation' : 'Generate Quotation' }}</button>
                    @if($quotationGenerated && $customerEmail)
                        <button type="submit" form="sendQuotationForm" class="btn btn-primary btn-lg" onclick="return confirm('Send the saved quotation to the customer? Unsaved changes will not be included.');"><i class="fas fa-paper-plane me-1"></i>Send to Customer</button>
                    @else
                        <button type="button" class="btn btn-secondary btn-lg" disabled title="{{ !$quotationGenerated ? 'Save the quotation first' : 'Customer email address is missing' }}"><i class="fas fa-paper-plane me-1"></i>Send to Customer</button>
                    @endif
                </div>
                @if(!$quotationGenerated)
                    <div class="alert alert-light border mt-3 mb-0">Generate and save the quotation before sending it to the customer.</div>
                @elseif(!$customerEmail)
                    <div class="alert alert-warning mt-3 mb-0">The customer has no email address.</div>
                @else
                    <div class="alert alert-success mt-3 mb-0">Quotation <strong>{{ $quotation->quotation_number ?? '#' . $quotation->id }}</strong> has been saved and can be sent to <strong>{{ $customerEmail }}</strong>.</div>
                @endif
            </form>

            @if($quotationGenerated && $customerEmail)
                <form id="sendQuotationForm" method="POST" action="{{ route('calibrations.send-quotation', $calibration) }}" class="d-none">@csrf</form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.quotation-page .card { border-radius: 12px; overflow: hidden; }
.quotation-page .card-header { padding: 1rem 1.25rem; }
.quotation-page .equipment-card { background: #fbfcff; }
.quotation-page .item-row { background: #fff; }
.quotation-page .item-row[data-source="requested"] { border-left: 4px solid #0d6efd !important; }
.quotation-page .form-label { margin-bottom: .35rem; }
.quotation-page .badge { white-space: normal; text-align: left; line-height: 1.35; }
@media (max-width: 575px) { .quotation-page .card-body { padding: .9rem; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    const container = document.getElementById('items-container');
    const form = document.getElementById('quotationForm');
    if (!container || !form) return;

    const onsiteSection = document.getElementById('onsite-costs-section');
    const field = id => document.getElementById(id);
    const value = id => Math.max(0, parseFloat(field(id)?.value) || 0);
    const setValue = (id, amount) => { if (field(id)) field(id).value = amount.toFixed(2); };
    const money = amount => 'KSh ' + amount.toLocaleString('en-KE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const setText = (id, text) => { if (field(id)) field(id).textContent = text; };

    // Always take the next index from the highest existing name, not the row count.
    let nextIndex = Math.max(0, ...Array.from(container.querySelectorAll('[name^="items["]')).map(input => {
        const match = input.name.match(/^items\[(\d+)\]/);
        return match ? Number(match[1]) + 1 : 0;
    }));

    function createRow(description = '', price = '') {
        const index = nextIndex++;
        const row = document.createElement('div');
        row.className = 'item-row border rounded p-3 mb-3';
        row.dataset.source = 'custom';
        row.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-lg-5"><label class="form-label small fw-semibold">Description</label><input type="text" name="items[${index}][description]" class="form-control description" required></div>
                <div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Quantity</label><input type="number" name="items[${index}][quantity]" class="form-control quantity" value="1" min="0.01" step="0.01" required></div>
                <div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Unit Price (KSh)</label><input type="number" name="items[${index}][unit_price]" class="form-control unit-price" min="0" step="0.01" required></div>
                <div class="col-9 col-lg-2"><label class="form-label small fw-semibold">Line Total</label><input type="text" class="form-control item-total" readonly></div>
                <div class="col-3 col-lg-1"><button type="button" class="btn btn-outline-danger w-100 remove-item" aria-label="Remove quotation line"><i class="fas fa-times"></i></button></div>
            </div>`;
        row.querySelector('.description').value = description;
        row.querySelector('.unit-price').value = price;
        container.appendChild(row);
        calculateTotals();
        row.querySelector('.description').focus();
    }

    field('addEquipmentItem')?.addEventListener('click', function () {
        const selector = field('equipmentTypeSelector');
        const option = selector?.selectedOptions[0];
        if (!option?.value) { alert('Select an equipment type first.'); return; }
        createRow('Calibration of ' + (option.dataset.name || option.textContent.trim()), option.dataset.rate || '');
        selector.value = '';
    });
    field('add-item')?.addEventListener('click', () => createRow());

    container.addEventListener('input', event => {
        if (event.target.matches('.quantity, .unit-price')) calculateTotals();
    });
    container.addEventListener('click', event => {
        const button = event.target.closest('.remove-item');
        if (!button) return;
        const row = button.closest('.item-row');
        if (row.dataset.source === 'requested' && !confirm('Remove this customer-requested equipment/service from the quotation?')) return;
        row.remove();
        calculateTotals();
    });

    function calculateOnsiteCosts() {
        if (!onsiteSection) return 0;
        const perDiem = value('numberOfStaff') * value('travelDays') * value('perDiemRate');
        const transport = value('transportCost');
        const other = value('otherCosts');
        setText('perDiemTotal', money(perDiem));
        setText('transportTotal', money(transport));
        setText('otherTotal', money(other));
        setText('onsiteTotal', money(perDiem + transport + other));
        return perDiem + transport + other;
    }

    function calculateTotals() {
        let subtotal = 0;
        container.querySelectorAll('.item-row').forEach(row => {
            const quantity = Math.max(0, parseFloat(row.querySelector('.quantity')?.value) || 0);
            const price = Math.max(0, parseFloat(row.querySelector('.unit-price')?.value) || 0);
            const lineTotal = Math.round((quantity * price + Number.EPSILON) * 100) / 100;
            row.querySelector('.item-total').value = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });
        subtotal = Math.round(subtotal * 100) / 100;
        const discountRate = Math.min(100, value('discountPercentage'));
        const taxRate = Math.min(100, value('taxRate'));
        const onsite = calculateOnsiteCosts();
        const discount = Math.round(subtotal * discountRate) / 100;
        const taxable = subtotal - discount;
        const tax = Math.round(taxable * taxRate) / 100;
        const total = Math.round((taxable + tax + onsite) * 100) / 100;
        setValue('subtotal', subtotal);
        setValue('discountAmountDisplay', discount);
        setValue('discountAmountHidden', discount);
        setValue('taxAmount', tax);
        setValue('onsiteSummary', onsite);
        setValue('totalAmount', total);
        setValue('totalAmountHidden', total);
    }

    field('discountPercentage')?.addEventListener('input', calculateTotals);
    onsiteSection?.addEventListener('input', calculateTotals);

    form.addEventListener('submit', function (event) {
        const rows = Array.from(container.querySelectorAll('.item-row'));
        const error = field('itemsError');
        if (!rows.length) {
            event.preventDefault();
            error.textContent = 'Add at least one quotation item.';
            error.classList.remove('d-none');
            return;
        }
        error.classList.add('d-none');
        calculateTotals();
    });
    calculateTotals();
});
</script>
@endpush
