<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use App\Models\EquipmentManufacturer;
use App\Models\KplcEquipmentType;
use App\Models\KplcServiceType;
use Illuminate\Contracts\View\View;
use App\Models\Equipment;

class LaboratoryConfigurationController extends Controller
{
    /**
     * Display the Laboratory Configuration dashboard.
     */
    public function index(): View
    {
        $serviceTypeCount = KplcServiceType::query()->count();

        $activeServiceTypeCount = KplcServiceType::query()
            ->where('status', true)
            ->count();

        $equipmentTypeCount = KplcEquipmentType::query()->count();

        $equipmentCategoryCount = EquipmentCategory::query()->count();

        $equipmentManufacturerCount =
            EquipmentManufacturer::query()->count();

        $recentServiceTypes = KplcServiceType::query()
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $recentEquipmentTypes = KplcEquipmentType::query()
            ->latest('updated_at')
            ->limit(5)
            ->get();

            $unconfiguredEquipmentCount =
    \App\Models\Equipment::query()
        ->where(function ($query) {
            $query
                ->whereNull('category_id')
                ->orWhereNull('manufacturer_id')
                ->orWhereNull('type_id');
        })
        ->count();
$equipmentConfiguration = [
    'total' =>
        Equipment::query()->count(),

    'complete' =>
        Equipment::query()
            ->configurationComplete()
            ->count(),

    'incomplete' =>
        Equipment::query()
            ->configurationIncomplete()
            ->count(),

    'missing_category' =>
        Equipment::query()
            ->whereNull('category_id')
            ->count(),

    'missing_manufacturer' =>
        Equipment::query()
            ->whereNull('manufacturer_id')
            ->count(),

    'missing_type' =>
        Equipment::query()
            ->whereNull('type_id')
            ->count(),
];
return view(
    'admin.laboratory-configuration.index',
    compact(
        'serviceTypeCount',
        'activeServiceTypeCount',
        'equipmentTypeCount',
        'equipmentCategoryCount',
        'equipmentManufacturerCount',
        'recentServiceTypes',
        'recentEquipmentTypes',
        'equipmentConfiguration',
        'unconfiguredEquipmentCount'
    )
);

    }
}
