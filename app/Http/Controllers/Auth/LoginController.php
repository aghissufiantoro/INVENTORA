<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Return the view for the login form
        return view('auth.login');
    }


public function login(Request $request)
{
    // Validasi input
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // Cek apakah username ada di database
    $user = User::where('username', $request->username)->first();

    if (!$user) {
        return back()->withErrors([
            'username' => 'Username tidak ditemukan.',
        ])->onlyInput('username');
    }

    // Jika username ditemukan, cek apakah password cocok
    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'password' => 'Password salah.',
        ])->onlyInput('username');
    }

    // Jika keduanya benar, login user
    Auth::login($user);

    return redirect()->route('dashboard.index');
}

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
