<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // User::create([
        //     'username'=>'pawanchhapola',
        //     'name' => "Pawan",
        //     'email' => "pawanchhapola123@gmail.com",
        //     'password' => Hash::make("1234567890"),
        // ]);

        // if (Auth::check()) {
        //     return redirect()->intended('/');
        // }
        // else {
        //     return view('auth.login');
        // }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('https://login.thepresence360.com/logout')->withCookies([
            Cookie::forget('fyear_start_date'),
            Cookie::forget('fyear_end_date'),
        ]);
    }
    // AuthController.php

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->intended('/');
    }

}
