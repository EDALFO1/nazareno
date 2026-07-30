<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function logear(Request $request)
    {
        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            $request->session()->flash('just_logged_in', true);

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciales incorrectas',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
