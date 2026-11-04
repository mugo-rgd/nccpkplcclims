<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Calibration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalibrationController extends Controller
{
    public function index()
    {
        $customer = Auth::user()->customer;
        $calibrations = Calibration::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.calibrations', compact('calibrations'));
    }
}
