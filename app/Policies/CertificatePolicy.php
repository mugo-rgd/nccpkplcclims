<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Certificate;

class CertificatePolicy
{
    /**
     * Determine if the user can view certificates.
     */
    public function viewCertificates(User $user)
    {
        return $user->hasRole(['supervisor', 'lab_manager', 'admin']);
    }

    /**
     * Determine if the user can view a specific certificate.
     */
    public function view(User $user, Certificate $certificate)
    {
        // Allow if user has the right role or is the one who generated it
        return $user->hasRole(['supervisor', 'lab_manager', 'admin']) ||
               $user->id === $certificate->generated_by;
    }

    /**
     * Determine if the user can approve a certificate.
     */
    public function approve(User $user, Certificate $certificate)
    {
        return $user->hasRole(['supervisor', 'lab_manager', 'admin'])
            && in_array($certificate->status, ['draft', 'pending', 'generated']);
    }

    /**
     * Determine if the user can reject a certificate.
     */
    public function reject(User $user, Certificate $certificate)
    {
        return $user->hasRole(['supervisor', 'lab_manager', 'admin'])
            && in_array($certificate->status, ['draft', 'pending', 'generated']);
    }

    /**
     * Determine if the user can modify a certificate.
     */
    public function modify(User $user, Certificate $certificate)
    {
        return $user->hasRole(['supervisor', 'lab_manager', 'admin'])
            && in_array($certificate->status, ['draft', 'pending', 'generated']);
    }

    /**
     * Determine if the user can issue a certificate.
     */
    public function issue(User $user, Certificate $certificate)
    {
        return $user->hasRole(['lab_manager', 'admin'])
            && $certificate->status === 'approved';
    }
}
