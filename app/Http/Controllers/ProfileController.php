<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's profile page.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $this->ensureInternalUser($user);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->ensureInternalUser($user);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'phone_number' => [
                'nullable',
                'string',
                'max:30',
            ],
            'staff_number' => [
                'nullable',
                'string',
                'max:50',
                'unique:users,staff_number,' . $user->id,
            ],
            'designation' => [
                'nullable',
                'string',
                'max:255',
            ],
            'department' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $user->fill($validated);
        $user->save();

        return back()->with(
            'success',
            'Profile details updated successfully.'
        );
    }

    /**
     * Upload or replace the authenticated user's signature.
     */
    public function uploadSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->ensureInternalUser($user);

        $validated = $request->validate([
            'signature' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
        ]);

        if (
            !empty($user->signature_path)
            && Storage::disk('public')->exists($user->signature_path)
        ) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $file = $validated['signature'];

        $safeName = str($user->name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        $filename =
            time() .
            '_' .
            ($safeName ?: 'user') .
            '_signature.' .
            $file->getClientOriginalExtension();

        $path = $file->storeAs(
            'signatures',
            $filename,
            'public'
        );

        $user->signature_path = $path;
        $user->signature_filename = $filename;
        $user->signature_updated_at = now();
        $user->save();

        return back()->with(
            'success',
            'Signature uploaded successfully.'
        );
    }

    /**
     * Delete the authenticated user's stored signature.
     */
    public function deleteSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        $this->ensureInternalUser($user);

        if (
            !empty($user->signature_path)
            && Storage::disk('public')->exists($user->signature_path)
        ) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $user->signature_path = null;
        $user->signature_filename = null;
        $user->signature_updated_at = null;
        $user->use_auto_signature = false;
        $user->save();

        return back()->with(
            'success',
            'Signature deleted successfully.'
        );
    }

    /**
     * Enable or disable automatic signature use.
     */
    public function updateAutoSignature(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $this->ensureInternalUser($user);

        $request->validate([
            'use_auto_signature' => [
                'nullable',
                'boolean',
            ],
        ]);

        $enabled = $request->boolean(
            'use_auto_signature'
        );

        if ($enabled && empty($user->signature_path)) {
            return back()->with(
                'error',
                'Upload a signature before enabling auto-signature.'
            );
        }

        $user->use_auto_signature = $enabled;
        $user->save();

        return back()->with(
            'success',
            $enabled
                ? 'Auto-signature enabled successfully.'
                : 'Auto-signature disabled successfully.'
        );
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        $this->ensureInternalUser($user);

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
        ]);

        $user->password = Hash::make(
            $validated['password']
        );

        $user->save();

        return back()->with(
            'success',
            'Password updated successfully.'
        );
    }

    /**
     * Restrict this profile management page to non-customer users.
     */
    private function ensureInternalUser($user): void
    {
        $isCustomer =
            $user->hasRole('customer')
            || $user->role === 'customer';

        abort_if(
            $isCustomer,
            403,
            'Customer accounts cannot access this profile page.'
        );
    }
}
