<?php

namespace App\Helpers;

class WorkflowHelper
{
    /**
     * Get workflow steps for display
     */
    public static function getWorkflowSteps($calibration)
    {
        return $calibration->getFlowSteps();
    }

    /**
     * Get workflow progress
     */
    public static function getProgress($calibration)
    {
        return $calibration->getProgressPercentage();
    }

    /**
     * Get next action button
     */
    public static function getNextActionButton($calibration)
    {
        $nextStatus = $calibration->getNextStatus();

        if (!$nextStatus) {
            return null;
        }

        $actions = [
            'quoted' => [
                'label' => 'Generate Quotation',
                'route' => 'calibrations.generate-quotation',
                'method' => 'POST',
                'icon' => 'fa-file-invoice',
                'permission' => 'create_calibrations',
                'condition' => !$calibration->customer->isKplc(),
            ],
            'paid' => [
                'label' => 'Mark Payment Received',
                'route' => 'calibrations.mark-payment',
                'method' => 'POST',
                'icon' => 'fa-money-bill-wave',
                'permission' => 'create_calibrations',
                'condition' => !$calibration->customer->isKplc(),
            ],
            'assigned' => [
                'label' => 'Assign Technician',
                'route' => '#',
                'method' => 'modal',
                'icon' => 'fa-user-check',
                'permission' => 'assign_calibrations',
                'condition' => true,
            ],
            'in_progress' => [
                'label' => 'Start Work',
                'route' => 'calibrations.start-work',
                'method' => 'POST',
                'icon' => 'fa-play',
                'permission' => 'perform_calibrations',
                'condition' => true,
            ],
            'testing' => [
                'label' => 'Add Test Results',
                'route' => '#',
                'method' => 'modal',
                'icon' => 'fa-flask',
                'permission' => 'perform_calibrations',
                'condition' => true,
            ],
            'completed' => [
                'label' => 'Generate Certificate',
                'route' => 'certificates.generate',
                'method' => 'POST',
                'icon' => 'fa-file-pdf',
                'permission' => 'perform_calibrations',
                'condition' => true,
            ],
        ];

        return $actions[$nextStatus] ?? null;
    }
}
