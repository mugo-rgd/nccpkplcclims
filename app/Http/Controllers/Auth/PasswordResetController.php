<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset request form
     */
    public function showRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)->first();

        // Generate reset token
        $token = Str::random(60);
        $user->password_reset_token = $token;
        $user->password_reset_requested_at = now();
        $user->save();

        // Send email
        try {
            $this->sendResetEmail($user, $token);
            return back()->with('success', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send reset email. Please try again.');
        }
    }

    /**
     * Send reset email
     */
    protected function sendResetEmail($user, $token)
    {
        $resetLink = route('password.reset.form', ['token' => $token, 'email' => $user->email]);

        $data = [
            'user' => $user,
            'resetLink' => $resetLink,
            'token' => $token,
        ];

        Mail::send('emails.password-reset', $data, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Password Reset Request - KPLC Meter Laboratory');
        });
    }

    /**
     * Show the password reset form
     */
    public function showResetForm(Request $request)
    {
        $token = $request->token;
        $email = $request->email;

        // Validate token
        $user = User::where('email', $email)
                    ->where('password_reset_token', $token)
                    ->where('password_reset_requested_at', '>=', now()->subHours(24))
                    ->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset token. Please request a new one.');
        }

        return view('auth.passwords.reset', compact('token', 'email'));
    }

    /**
     * Reset the password
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>])/'
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least 1 capital letter and 1 special character.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('email', $request->email)
                    ->where('password_reset_token', $request->token)
                    ->where('password_reset_requested_at', '>=', now()->subHours(24))
                    ->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset token. Please request a new one.');
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->password_reset_requested_at = null;
        $user->password_reset_completed_at = now();
        $user->save();

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully! Please login with your new password.');
    }

    /**
     * Resend reset link
     */
    public function resendLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid email address.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Generate new token
        $token = Str::random(60);
        $user->password_reset_token = $token;
        $user->password_reset_requested_at = now();
        $user->save();

        try {
            $this->sendResetEmail($user, $token);
            return response()->json(['success' => 'Reset link has been resent to your email.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send reset email.'], 500);
        }
    }
}
