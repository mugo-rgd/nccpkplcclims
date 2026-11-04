<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $maxAttempts = 5;
protected $decayMinutes = 5;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override the redirect path based on user role.
     */
    protected function redirectTo()
    {
        if (auth()->user()->role === 'customer') {
            return route('customer.dashboard');
        }

        return route('dashboard');
    }
}
