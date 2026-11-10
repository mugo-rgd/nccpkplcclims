<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Maximum login attempts.
     */
    protected $maxAttempts = 5;

    /**
     * Lockout period in minutes.
     */
    protected $decayMinutes = 5;

    /**
     * Default redirect.
     */
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Reject inactive customer accounts before
     * Laravel creates an authenticated session.
     *
     * Existing staff login behavior is preserved.
     */
    protected function credentials(Request $request)
    {
        return [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_active' => true,
        ];
    }

    /**
     * Handle failed authentication.
     *
     * Use a generic message to avoid revealing
     * whether an email address is registered.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() =>
                [trans('auth.failed')],
        ]);
    }

    /**
     * Redirect authenticated users based on role.
     */
    protected function redirectTo()
    {
        $user = auth()->user();

        if ($user && $user->hasRole('customer')) {
            return route('customer.dashboard');
        }

        return route('dashboard');
    }
}
