<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class POSAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('pos.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (auth()->user()->isAdmin()) {
                return redirect()->intended(
                    $request->input('redirect', 'pos.index') === 'admin' 
                        ? '/admin' 
                        : route('pos.index')
                );
            }

            return redirect()->route('pos.index');
        }

        return back()->withErrors([
            'email' => 'Kredensial tidak valid.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}