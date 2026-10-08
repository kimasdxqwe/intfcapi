<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticationController extends Controller
{
    /**
     * Show the login form.
     */
    public function index(Request $request)
    {
        $redirect = $request->query('redirect');

        if (is_string($redirect)
            && str_starts_with($redirect, '/')
            && ! str_starts_with($redirect, '//')
            && ! str_contains($redirect, '\\')) {
            $request->session()->put('url.intended', url($redirect));
        }

        return view('auth.login');
    }

    /**
     * Handle a login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {

            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->withFragment('login-section')
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        // Sends the user back to whatever protected route they were
        // trying to reach before being redirected to login — this is
        // what carries them back to /oauth/authorize with all its
        // original query parameters intact.
        return redirect()->intended('/');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect('/login');
    }
}
