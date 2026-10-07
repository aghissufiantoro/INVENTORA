<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // Tampilkan form lupa password
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem'
        ]);

        $user = User::where('email', $request->email)->first();

        $token = Str::random(64);

        $user->update([
            'reset_token' => $token,
            'reset_token_expires_at' => Carbon::now()->addHour()
        ]);

        try {
            Mail::send('emails.reset-password', [
                'token' => $token,
                'name' => $user->name
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            Log::error('Email error: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengirim email. ' . $e->getMessage());
        }

        return back()->with('success', 'Link reset password telah dikirim ke email Anda!');
    }


    // Tampilkan form reset password
    public function showResetForm($token)
    {
        // Cek apakah token valid
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires_at', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Token tidak valid atau sudah kadaluarsa!');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $user->email
        ]);
    }

    // Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required'
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 8 karakter'
        ]);

        // Cek token & email
        $user = User::where('email', $request->email)
            ->where('reset_token', $request->token)
            ->first();

        if (!$user) {
            return back()->with('error', 'Token atau email tidak valid!');
        }

        // Cek expired
        if (Carbon::now()->greaterThan($user->reset_token_expires_at)) {
            return back()->with('error', 'Token sudah kadaluarsa!');
        }

        // 🔥 Cek password lama vs baru (AMBIL ASLI DARI DB)
        if (Hash::check($request->password, $user->getOriginal('password'))) {
            return back()
                ->withInput()
                ->with('error', 'Password baru tidak boleh sama dengan password lama.');
        }

        // Update password (cast hashed otomatis jalan)
        $user->password = $request->password;
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login.');
    }
}