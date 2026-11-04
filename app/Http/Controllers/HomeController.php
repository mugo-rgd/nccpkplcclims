<?php

namespace App\Http\Controllers;

use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Display the public Meter Central Laboratory landing page.
     *
     * This page is deliberately public. A visitor does not need to
     * authenticate before viewing services and indicative prices.
     */
    public function index()
{
    $serviceTypes = KplcServiceType::active()
        ->orderBy('serv_code')
        ->get();

    $equipmentTypes = KplcEquipmentType::orderBy('name')
        ->get();

    return view('welcome', compact(
        'serviceTypes',
        'equipmentTypes'
    ));
}
}
