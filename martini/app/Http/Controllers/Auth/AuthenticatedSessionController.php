<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Notifications\TwoFactorCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->disabled == 0)
        {
            if ($user->use_two_factor == 0)
            {
                $request->session()->regenerate();
                session_start();
                $_SESSION['USER'] = $user->id;
                session_write_close();
                return redirect()->intended(RouteServiceProvider::HOME);
            }
            else
            {
                $user->generateTwoFactorCode();
                $user->notify(new TwoFactorCode());
                return redirect()->route('verify.index');
            }
        }
        else
        {
            return redirect()->intended("/");
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        session_start();
        session_destroy();
        return redirect('/');
    }
}
