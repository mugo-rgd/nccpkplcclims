@extends('layouts.app')

@section('title', 'Generate Quotation')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Generate Quotation</h1>
        <div>
            <a href="{{ route('calibrations.show', $calibration) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-invoice"></i> Quotation Details
            </h6>
        </div>
        <div class="card-body">
    <form method="POST" action="{{ route('calibrations.store-quotation', $calibration->id) }}">
    @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Customer</label>
                            <input type="text" class="form-control" value="{{ $calibration->customer->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Equipment</label>
                            <input type="text" class="form-control" value="{{ $calibration->equipment_name }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Serial Number</label>
                            <input type="text" class="form-control" value="{{ $calibration->serial_number }}" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Equipment Type</label>
                            <input type="text" class="form-control" value="{{ $calibration->equipment_type_name }}" disabled>
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="font-weight-bold mb-3">Quotation Items</h6>

                <div id="items-container">
                    <div class="row item-row mb-2">
                        <div class="col-md-5">
                            <input type="text" name="items[0][description]" class="form-control"
                                   placeholder="Description" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[0][quantity]" class="form-control quantity"
                                   placeholder="Qty" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[0][unit_price]" class="form-control unit-price"
                                   placeholder="Unit Price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control item-total" placeholder="Total" disabled>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-item">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-primary mb-3" id="add-item">
                    <i class="fas fa-plus"></i> Add Item
                </button>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Subtotal</label>
                            <input type="text" class="form-control" id="subtotal" value="0.00" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Discount Percentage (%)</label>
                            <input type="number" name="discount_percentage" class="form-control"
                                   id="discountPercentage" value="0" step="0.01" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Discount Amount (KSh)</label>
                            <input type="number" name="discount_amount" class="form-control"
                                   id="discountAmount" value="0" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Tax Rate (%)</label>
                            <input type="number" name="tax_rate" class="form-control"
                                   id="taxRate" value="16" step="0.01" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Tax Amount (KSh)</label>
                            <input type="text" class="form-control" id="taxAmount" value="0.00" disabled>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="fw-bold">Total Amount (KSh)</label>
                            <input type="text" class="form-control" id="totalAmount" value="0.00" disabled>
                            <input type="hidden" name="total_amount" id="totalAmountHidden" value="0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control"
                                   value="{{ now()->addDays(30)->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="fw-bold">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="2">Payment due within 30 days.</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="fw-bold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group text-right">
                 <button type="submit" class="btn btn-success">
        <i class="fas fa-file-invoice"></i> Generate Quotation
    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;

    // Add item row
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newRow = document.createElement('div');
        newRow.className = 'row item-row mb-2';
        newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="items[${itemIndex}][description]" class="form-control"
                       placeholder="Description" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity"
                       placeholder="Qty" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price"
                       placeholder="Unit Price" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <input type="text" class="form-control item-total" placeholder="Total" disabled>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-item">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        itemIndex++;

        // Add event listeners to new row
        newRow.querySelector('.quantity').addEventListener('input', calculateTotals);
        newRow.querySelector('.unit-price').addEventListener('input', calculateTotals);
        newRow.querySelector('.remove-item').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                newRow.remove();
                calculateTotals();
            }
        });
    });

    // Calculate totals
    function calculateTotals() {
        const rows = document.querySelectorAll('.item-row');
        let subtotal = 0;

        rows.forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
            const total = quantity * unitPrice;
            row.querySelector('.item-total').value = total.toFixed(2);
            subtotal += total;
        });

        const discountPercent = parseFloat(document.getElementById('discountPercentage').value) || 0;
        const discountAmount = parseFloat(document.getElementById('discountAmount').value) || 0;
        const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;

        let discount = discountAmount;
        if (discountPercent > 0) {
            discount = (subtotal * discountPercent) / 100;
        }

        const afterDiscount = subtotal - discount;
        const taxAmount = (afterDiscount * taxRate) / 100;
        const total = afterDiscount + taxAmount;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('taxAmount').value = taxAmount.toFixed(2);
        document.getElementById('totalAmount').value = total.toFixed(2);
        document.getElementById('totalAmountHidden').value = total.toFixed(2);
    }

    // Event listeners
    document.querySelectorAll('.quantity, .unit-price').forEach(el => {
        el.addEventListener('input', calculateTotals);
    });

    document.getElementById('discountPercentage').addEventListener('input', calculateTotals);
    document.getElementById('discountAmount').addEventListener('input', calculateTotals);
    document.getElementById('taxRate').addEventListener('input', calculateTotals);

    // Remove item
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                this.closest('.item-row').remove();
                calculateTotals();
            }
        });
    });

    // Initial calculation
    calculateTotals();
});
</script>
@endpush
