<?php

namespace App\Http\Controllers\Auth;

use App\Notifications\TwoFactorCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.twoFactor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'two_factor_secret' => 'string|required',
        ]);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if($request->input('two_factor_secret') == $user->two_factor_secret)
        {
            $user->resetTwoFactorCode();
            $request->session()->regenerate();
            session_start();
            $_SESSION['USER'] = $user->id;
            session_write_close();
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return redirect()->back()
            ->withErrors(['two_factor_secret' =>
                'The two factor code you have entered does not match']);
    }

    public function resend()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->generateTwoFactorCode();
        $user->notify(new TwoFactorCode());

        return redirect()->back()->withMessage('The two factor code has been sent again');
    }
}
