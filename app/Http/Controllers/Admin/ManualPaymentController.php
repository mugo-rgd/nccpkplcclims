<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Quote;
use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Validator;


class ManualPaymentController extends Controller
{
    private const ROLES = ['super_admin', 'lab_manager', 'supervisor', 'finance', 'accounts'];

    private function authorizeStaff(Request $request, string $permission): void
    {
        abort_unless($request->user()?->hasAnyRole(self::ROLES) && $request->user()?->can($permission), 403);
    }

    /** Cross-quotation finance overview. Only confirmed entries count as received. */
    public function dashboard(Request $request): View
    {
        $this->authorizeStaff($request, 'payments.view');

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
            'method' => ['nullable', Rule::in(['bank_transfer', 'cash', 'cheque', 'mpesa'])],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $transactions = PaymentTransaction::query()
            ->with(['quote:id,quote_number,customer_id,status,total_amount,balance_due', 'recorder:id,name', 'confirmer:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['method'] ?? null, fn ($q, $v) => $q->where('method', $v))
            ->when($filters['search'] ?? null, function ($q, $value) {
                $q->where(function ($query) use ($value) {
                    $query->where('reference', 'like', '%' . $value . '%')
                        ->orWhereHas('quote', fn ($quote) => $quote->where('quote_number', 'like', '%' . $value . '%'));
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'confirmed_total' => PaymentTransaction::query()->where('status', PaymentTransaction::CONFIRMED)->sum('amount'),
            'pending_count' => PaymentTransaction::query()->where('status', PaymentTransaction::PENDING)->count(),
            'confirmed_count' => PaymentTransaction::query()->where('status', PaymentTransaction::CONFIRMED)->count(),
            'outstanding_total' => Quote::query()->where('is_external', true)
                ->whereIn('status', ['sent', 'partial_paid'])
                ->sum('balance_due'),
        ];

        return view('admin.payments.dashboard', compact('transactions', 'summary'));
    }

    public function index(Request $request, Quote $quote): View
    {
        $this->authorizeStaff($request, 'payments.view');
       
abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission.'
);
        $payments = PaymentTransaction::query()->where('quote_id', $quote->id)
            ->with(['recorder:id,name', 'confirmer:id,name'])->latest()->paginate(20);
        return view('admin.payments.index', compact('quote', 'payments'));
    }

    public function store(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorizeStaff($request, 'payments.record');
       
abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission.'
);
        $data = $request->validate([
            'method' => ['required', Rule::in(['bank_transfer', 'cash', 'cheque'])],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'reference' => [
    'required',
    'string',
    'max:120',
    function ($attribute, $value, $fail) use ($request) {
        $exists = PaymentTransaction::query()
            ->where('method', $request->input('method'))
            ->where('reference', trim($value))
            ->exists();

        if ($exists) {
            $fail(
                'This payment reference has already been recorded for '
                . str_replace('_', ' ', $request->input('method'))
                . '. Please review the existing transaction instead of recording it again.'
            );
        }
    },
],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'clearance' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $quote->refresh();
        if (!$quote->canReceivePayment()) {
            throw ValidationException::withMessages(['quote' => 'Only sent, valid external quotations with a balance can receive payments.']);
        }
        if ((int) round($data['amount'] * 100) > (int) round($quote->balance_due * 100)) {
            throw ValidationException::withMessages(['amount' => 'Amount exceeds current outstanding balance.']);
        }
        $receiptPath = $request->file('receipt')->store('payment-receipts', 'local');
        $clearancePath = $request->hasFile('clearance') ? $request->file('clearance')->store('payment-clearances', 'local') : null;
        try {
            PaymentTransaction::query()->create([
                'quote_id' => $quote->id, 'method' => $data['method'], 'status' => PaymentTransaction::PENDING,
                'amount' => $data['amount'], 'currency' => 'KES', 'reference' => trim($data['reference']),
                'payment_date' => $data['payment_date'], 'receipt_path' => $receiptPath,
                'clearance_path' => $clearancePath, 'notes' => $data['notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);
        } catch (UniqueConstraintViolationException $e) {

    Storage::disk('local')->delete(
        array_filter([$receiptPath, $clearancePath])
    );

    throw ValidationException::withMessages([
        'reference' => 'This payment reference has already been recorded. Please review the existing transaction.',
    ]);

} catch (\Throwable $e) {

    Storage::disk('local')->delete(
        array_filter([$receiptPath, $clearancePath])
    );

    throw $e;
}
        return back()->with('success', 'Payment recorded and awaiting independent confirmation.');
    }

    public function confirm(Request $request, PaymentTransaction $payment, PaymentReconciliationService $service): RedirectResponse
    {
        $this->authorizeStaff($request, 'payments.confirm');
       
abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission to confirm payments.'
);
        DB::transaction(function () use ($payment, $request, $service): void {
            $quote = Quote::query()->lockForUpdate()->findOrFail($payment->quote_id);
            $entry = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);
            abort_unless($entry->method !== 'mpesa', 403);
            if ($entry->status !== PaymentTransaction::PENDING) {
                throw ValidationException::withMessages(['payment' => 'Payment is no longer pending.']);
            }
            if ((int) $entry->recorded_by === (int) $request->user()->id) {
                throw ValidationException::withMessages(['payment' => 'You cannot confirm a payment you recorded.']);
            }
            if (!$quote->canReceivePayment()) {
                throw ValidationException::withMessages(['quote' => 'Quotation is not eligible for payment.']);
            }
            if ($entry->method === 'cheque' && !$entry->clearance_path) {
                throw ValidationException::withMessages(['clearance' => 'Cheque clearance evidence is required before confirmation.']);
            }
            if ((int) round((float) $entry->amount * 100) > (int) round((float) $quote->balance_due * 100)) {
                throw ValidationException::withMessages(['amount' => 'Payment exceeds remaining balance.']);
            }
            $entry->forceFill(['status' => PaymentTransaction::CONFIRMED, 'confirmed_by' => $request->user()->id, 'confirmed_at' => now()])->save();
            $service->reconcile($quote->id);
        }, 3);
        return back()->with('success', 'Payment confirmed and quotation balance recalculated.');
    }

    public function reject(Request $request, PaymentTransaction $payment): RedirectResponse
    {
        $this->authorizeStaff($request, 'payments.reject');
        
abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission to reject payments.'
);
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);
        DB::transaction(function () use ($payment, $data): void {
            $entry = PaymentTransaction::query()->lockForUpdate()->findOrFail($payment->id);
            abort_unless($entry->method !== 'mpesa' && $entry->status === PaymentTransaction::PENDING, 409);
            $entry->forceFill(['status' => PaymentTransaction::REJECTED, 'rejection_reason' => $data['rejection_reason'], 'rejected_at' => now()])->save();
        });
        return back()->with('success', 'Payment rejected; quotation balance unchanged.');
    }

    public function receipt(Request $request, PaymentTransaction $payment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorizeStaff($request, 'payments.receipts.download');
        
abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission to receipt payments.'
);
        abort_unless($payment->receipt_path && Storage::disk('local')->exists($payment->receipt_path), 404);
        return Storage::disk('local')->download($payment->receipt_path);
    }

    public function clearance(Request $request, PaymentTransaction $payment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorizeStaff($request, 'payments.receipts.download');
        

abort_unless(
    $request->user()->can('payments.confirm'),
    403,
    'You do not have permission to clear payments.'
);
        abort_unless($payment->clearance_path && Storage::disk('local')->exists($payment->clearance_path), 404);
        return Storage::disk('local')->download($payment->clearance_path);
    }
}
