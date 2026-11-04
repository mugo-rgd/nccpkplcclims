<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\JobCard;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Customer resolver
    |--------------------------------------------------------------------------
    |
    | Every customer-portal method uses this method instead of repeatedly
    | calling:
    |
    |     Auth::user()->customer
    |
    | This prevents "Attempt to read property id on null".
    |
    */
    private function currentCustomer(): Customer
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless(
            $user,
            401,
            'You must be logged in to access the customer portal.'
        );

        /*
         * Primary relationship:
         *
         * User::customer()
         */
        $customer = $user->customer;

        /*
         * Optional fallback for systems where users.customer_id exists.
         */
        if (!$customer) {
            $customerId = $user->getAttribute('customer_id');

            if ($customerId) {
                $customer = Customer::query()
                    ->find($customerId);
            }
        }

        abort_unless(
            $customer,
            403,
            'Your login account is not linked to a customer profile. Please contact the Meter Central Laboratory administrator.'
        );

        return $customer;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * Display the customer dashboard.
     */
    public function index(): View
    {
        $customer = $this->currentCustomer();

        /*
        |--------------------------------------------------------------------------
        | Customer calibrations
        |--------------------------------------------------------------------------
        */

        $calibrations = Calibration::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'equipment',
                'performedBy',
                'equipmentType',
            ])
            ->latest('created_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' =>
                $calibrations->count(),

            'pending' =>
                $calibrations
                    ->where('status', 'pending')
                    ->count(),

            'quoted' =>
                $calibrations
                    ->where('status', 'quoted')
                    ->count(),

            'pending_payment' =>
                $calibrations
                    ->where(
                        'status',
                        'pending_payment'
                    )
                    ->count(),

            'paid' =>
                $calibrations
                    ->where('status', 'paid')
                    ->count(),

            'in_progress' =>
                $calibrations
                    ->whereIn(
                        'status',
                        [
                            'assigned',
                            'in_progress',
                            'testing',
                        ]
                    )
                    ->count(),

            'completed' =>
                $calibrations
                    ->whereIn(
                        'status',
                        [
                            'completed',
                            'certified',
                        ]
                    )
                    ->count(),

            'rejected' =>
                $calibrations
                    ->where('status', 'rejected')
                    ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Recent calibrations
        |--------------------------------------------------------------------------
        */

        $recentCalibrations =
            $calibrations->take(5);

        /*
        |--------------------------------------------------------------------------
        | Recent quotations
        |--------------------------------------------------------------------------
        */

        $quotes = Quote::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'calibration',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent certificates
        |--------------------------------------------------------------------------
        */

        $certificates = Certificate::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'calibration',
                'generatedBy',
                'checkedBy',
                'approvedBy',
            ])
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent job cards
        |--------------------------------------------------------------------------
        */

        $jobCards = JobCard::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Delivery notes
        |--------------------------------------------------------------------------
        */

        $deliveryNotes = Calibration::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->whereNotNull(
                'delivery_number'
            )
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Collection notes
        |--------------------------------------------------------------------------
        */

        $collectionNotes = Calibration::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->whereNotNull(
                'collection_number'
            )
            ->latest('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Request forms
        |--------------------------------------------------------------------------
        */

        $requestForms = Calibration::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view(
            'customer.dashboard',
            compact(
                'customer',
                'calibrations',
                'stats',
                'recentCalibrations',
                'quotes',
                'certificates',
                'jobCards',
                'deliveryNotes',
                'collectionNotes',
                'requestForms'
            )
        );
    }

    /**
     * View a specific calibration.
     */
    public function viewCalibration(
        int $id
    ): View {
        $customer = $this->currentCustomer();

        /*
         * Important:
         * Customer ID is included in the query so a customer cannot
         * access another customer's calibration simply by changing
         * the URL ID.
         */
        $calibration = Calibration::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'customer',
                'equipment',
                'performedBy',
                'equipmentType',
                'testResults',
            ])
            ->findOrFail($id);

        $quote = Quote::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->where(
                'calibration_id',
                $calibration->id
            )
            ->latest('id')
            ->first();

        $certificate = Certificate::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->where(
                'calibration_id',
                $calibration->id
            )
            ->latest('id')
            ->first();

        $jobCard = JobCard::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->where(
                'calibration_id',
                $calibration->id
            )
            ->latest('id')
            ->first();

        return view(
            'customer.calibrations.show',
            compact(
                'customer',
                'calibration',
                'quote',
                'certificate',
                'jobCard'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    /**
     * Display customer quotations.
     */
   public function quotes(): View
{
    $customer = $this->currentCustomer();

    $quotes = Quote::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->with([
            'calibration',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.quotes.index',
        compact(
            'customer',
            'quotes'
        )
    );
}

    /**
     * View a specific quotation.
     */
    public function viewQuote(
        int $id
    ): View {
        $customer = $this->currentCustomer();

        $quote = Quote::query()
            ->where(
                'customer_id',
                $customer->id
            )
            ->with([
                'customer',
                'calibration',
                'calibration.equipment',
                'creator',
                'approver',
            ])
            ->findOrFail($id);

        return view(
            'customer.quotes.show',
            compact(
                'customer',
                'quote'
            )
        );
    }



// ==============customer controller==============
public function deliveryNotes(): View
{
    $customer = $this->currentCustomer();

    $deliveryNotes = Calibration::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->whereNotNull(
            'delivery_number'
        )
        ->whereNotNull(
            'delivery_note_path'
        )
        ->with([
            'equipment',
            'equipmentType',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.delivery-notes.index',
        compact(
            'customer',
            'deliveryNotes'
        )
    );
}

public function collectionNotes(): View
{
    $customer = $this->currentCustomer();

    $collectionNotes = Calibration::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->whereNotNull(
            'collection_number'
        )
        ->with([
            'equipment',
            'equipmentType',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.collection-notes.index',
        compact(
            'customer',
            'collectionNotes'
        )
    );
}

public function requestForms(): View
{
    $customer = $this->currentCustomer();

    $requestForms = Calibration::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->with([
            'equipment',
            'equipmentType',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.request-forms.index',
        compact(
            'customer',
            'requestForms'
        )
    );
}

public function certificates(): View
{
    $customer = $this->currentCustomer();

    $certificates = Certificate::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->whereIn(
            'status',
            [
                'approved',
                'issued',
                'sent',
            ]
        )
        ->with([
            'calibration',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.certificates.index',
        compact(
            'customer',
            'certificates'
        )
    );
}

public function viewCertificate(
    int $id
): View {
    $customer = $this->currentCustomer();

    $certificate = Certificate::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->whereIn(
            'status',
            [
                'approved',
                'issued',
                'sent',
            ]
        )
        ->with([
            'calibration',
            'generatedBy',
            'checkedBy',
            'approvedBy',
            'issuedBy',
            'sentBy',
        ])
        ->findOrFail($id);

    return view(
        'customer.certificates.show',
        compact(
            'customer',
            'certificate'
        )
    );
}

public function calibrations(
    Request $request
): View {
    $customer = $this->currentCustomer();

    $query = Calibration::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->with([
            'equipment',
            'performedBy',
            'equipmentType',
        ]);

    if ($request->filled('status')) {
        $query->where(
            'status',
            $request->input('status')
        );
    }

    if ($request->filled('search')) {
        $search = trim(
            (string) $request->input('search')
        );

        $query->where(
            function ($query) use ($search): void {
                $query
                    ->where(
                        'equipment_name',
                        'LIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'serial_number',
                        'LIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'manufacturer',
                        'LIKE',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'model',
                        'LIKE',
                        "%{$search}%"
                    );
            }
        );
    }

    $calibrations = $query
        ->latest('created_at')
        ->paginate(15)
        ->withQueryString();

    return view(
        'customer.calibrations.index',
        compact(
            'customer',
            'calibrations'
        )
    );
}

public function jobCards(): View
{
    $customer = $this->currentCustomer();

    $jobCards = JobCard::query()
        ->where(
            'customer_id',
            $customer->id
        )
        ->with([
            'calibration.performedBy',
            'allocatedTo',
        ])
        ->latest('created_at')
        ->paginate(15);

    return view(
        'customer.job-cards.index',
        compact(
            'customer',
            'jobCards'
        )
    );
}
}
