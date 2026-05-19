<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\SystemSetting;
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
            /**
            *    .env and db table system_setting add global toggle for 2FA
            */
            $tfa = SystemSetting::where("key_name","TWO_FACTOR_ENABLED")->first();
            if (env("TWO_FACTOR_ENABLED",true) && $tfa->key_value == true && $user->use_two_factor == 1)
            {
                $user->generateTwoFactorCode();
                $user->notify(new TwoFactorCode());
                return redirect()->route('verify.index');
            }
            $request->session()->regenerate();
            session_start();
            $_SESSION['USER'] = $user->id;
            session_write_close();
            return redirect()->intended(RouteServiceProvider::HOME);
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
