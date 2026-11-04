<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfigurationAuditController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $action = trim(
            (string) $request->input('action', '')
        );

        $activities = ActivityLog::query()
            ->with('user')
            ->whereIn('subject_type', [
                \App\Models\KplcEquipmentType::class,
                \App\Models\KplcServiceType::class,
                \App\Models\EquipmentCategory::class,
                \App\Models\EquipmentManufacturer::class,
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where(
                                'description',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                fn ($userQuery) =>
                                    $userQuery->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                            );
                    });
                }
            )
            ->when(
                $action !== '',
                fn ($query) =>
                    $query->where('action', $action)
            )
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        $actions = ActivityLog::query()
            ->whereIn('subject_type', [
                \App\Models\KplcEquipmentType::class,
                \App\Models\KplcServiceType::class,
                \App\Models\EquipmentCategory::class,
                \App\Models\EquipmentManufacturer::class,
            ])
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view(
            'admin.laboratory-configuration.audit',
            compact(
                'activities',
                'actions',
                'search',
                'action'
            )
        );
    }
}
