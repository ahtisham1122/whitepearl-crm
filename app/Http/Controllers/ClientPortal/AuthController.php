<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientPortal\ClientLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the client login form.
     */
    public function create(): View
    {
        return view('client-portal.auth.login');
    }

    /**
     * Log a client in, using the 'client' guard only.
     */
    public function store(ClientLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('client.dashboard', absolute: false));
    }

    /**
     * Log the client out of the 'client' guard. This never touches the
     * staff 'web' guard, so logging out of the portal can't sign a staff
     * member out of the admin side (or the reverse).
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    /**
     * "Forgot password" is admin-controlled only -- there is no
     * self-service reset. This page just tells the client to contact the
     * office, which then resets their password from the client's admin
     * page and shares the new one directly.
     */
    public function forgotPassword(): View
    {
        return view('client-portal.auth.forgot-password');
    }
}
