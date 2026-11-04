<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerTaxDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CustomerVatVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query()->whereIn('vat_exemption_status', ['pending', 'approved', 'rejected']);
        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('vat_exemption_status', $request->status);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $customers = $query->orderByRaw("CASE vat_exemption_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->latest()->paginate(20)->withQueryString();
        return view('admin.vat-verification.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->load(['taxDocuments' => fn ($q) => $q->latest(), 'user']);
        return view('admin.vat-verification.show', compact('customer'));
    }

    public function download(Customer $customer, CustomerTaxDocument $document)
    {
        abort_unless($document->customer_id === $customer->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);
        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }

    public function verifyDocument(Request $request, Customer $customer, CustomerTaxDocument $document)
    {
        abort_unless($document->customer_id === $customer->id, 404);
        $data = $request->validate([
            'verification_status' => ['required', 'in:approved,rejected'],
            'verification_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($customer, $document, $data, $request) {
            $lockedCustomer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($lockedCustomer->vat_exemption_status === 'approved') {
                throw ValidationException::withMessages(['verification_status' => 'An approved exemption must be revoked before its documents can be changed.']);
            }
            $document->update([
                'verification_status' => $data['verification_status'],
                'verification_notes' => $data['verification_notes'] ?? null,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);
        });
        return back()->with('success', 'Document review saved.');
    }

    public function approve(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_vat_rate' => ['required', 'numeric', 'between:0,100'],
            'vat_verification_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($customer, $data, $request) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($customer->vat_exemption_status !== 'pending') {
                throw ValidationException::withMessages(['customer_vat_rate' => 'Only pending requests may be approved.']);
            }
            if (blank($customer->tax_id) || blank($customer->vat_exemption_certificate_number)
                || !$customer->vat_exemption_expiry_date
                || $customer->vat_exemption_expiry_date->startOfDay()->lt(today())) {
                throw ValidationException::withMessages(['customer_vat_rate' => 'A valid KRA PIN, certificate number and unexpired exemption date are required.']);
            }
            $documents = $customer->taxDocuments()->get();
            foreach (['kra_pin_certificate', 'vat_exemption_certificate'] as $type) {
                $latest = $documents->where('document_type', $type)->sortByDesc('id')->first();
                if (!$latest || $latest->verification_status !== 'approved' || !$latest->verified_by || !$latest->verified_at) {
                    throw ValidationException::withMessages(['customer_vat_rate' => "The latest {$type} must be verified and approved first."]);
                }
            }
            $customer->update([
                'vat_exemption_status' => 'approved',
                'customer_vat_rate' => $data['customer_vat_rate'],
                'vat_verified_by' => $request->user()->id,
                'vat_verified_at' => now(),
                'vat_verification_notes' => $data['vat_verification_notes'] ?? null,
            ]);
        });
        return redirect()->route('lab-manager.vat.show', $customer)->with('success', 'VAT rate approved.');
    }

    public function reject(Request $request, Customer $customer)
    {
        $data = $request->validate(['vat_verification_notes' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($customer, $data) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($customer->vat_exemption_status !== 'pending') {
                throw ValidationException::withMessages(['vat_verification_notes' => 'Only pending requests can be rejected.']);
            }
            $customer->update([
                'vat_exemption_status' => 'rejected',
                'customer_vat_rate' => null,
                'vat_verified_by' => null,
                'vat_verified_at' => null,
                'vat_verification_notes' => $data['vat_verification_notes'],
            ]);
        });
        return back()->with('success', 'VAT exemption request rejected.');
    }

    public function revoke(Request $request, Customer $customer)
    {
        $data = $request->validate(['vat_verification_notes' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($customer, $data) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($customer->vat_exemption_status !== 'approved') {
                throw ValidationException::withMessages(['vat_verification_notes' => 'Only approved exemptions can be revoked.']);
            }
            $customer->update([
                'vat_exemption_status' => 'rejected',
                'customer_vat_rate' => null,
                'vat_verified_by' => null,
                'vat_verified_at' => null,
                'vat_verification_notes' => $data['vat_verification_notes'],
            ]);
        });
        return back()->with('success', 'VAT relief revoked. Standard VAT now applies.');
    }
}
