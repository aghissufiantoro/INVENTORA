<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class CustomPasswordResetController extends Controller
{
    // 1. Tampilkan form input email
    public function showForgotForm()
    {
        return view('auth.passwords.forgot');
    }

    // 2. Kirim OTP ke email
    public function sendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email tidak ditemukan dalam sistem.'
        ]);

        // Generate OTP 6 digit
        $otp = rand(100000, 999999);
        $token = Str::random(60);

        // Hapus OTP lama jika ada
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Simpan OTP baru
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'otp_code' => $otp,
            'created_at' => Carbon::now()
        ]);

        // Kirim email
        try {
            Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Kode OTP Reset Password');
            });

            return redirect()->route('password.verify.form')
                ->with('email', $request->email)
                ->with('success', 'Kode OTP telah dikirim ke email Anda!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email. Pastikan koneksi internet aktif.']);
        }
    }

    // 3. Tampilkan form verifikasi OTP
    public function showVerifyForm()
    {
        if (!session('email')) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.verify-otp');
    }

    // 4. Verifikasi OTP
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|digits:6'
        ]);

        // Cek OTP di database
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak valid.']);
        }

        // Cek apakah OTP sudah kadaluarsa (60 menit)
        $createdAt = Carbon::parse($passwordReset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['otp_code' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.']);
        }

        // OTP valid, redirect ke form reset password
        return redirect()->route('password.reset.form')
            ->with('email', $request->email)
            ->with('verified', true);
    }

    // 5. Tampilkan form reset password
    public function showResetForm()
    {
        if (!session('verified')) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.reset');
    }

    // 6. Proses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal 8 karakter.'
        ]);

        // Cek apakah email masih valid di password_reset_tokens
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'Sesi reset password tidak valid.']);
        }

        // Update password user
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus data di password_reset_tokens
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.');
    }

    // 7. Kirim ulang OTP
    public function resendOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Generate OTP baru
        $otp = rand(100000, 999999);
        $token = Str::random(60);

        // Update OTP
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'otp_code' => $otp,
            'created_at' => Carbon::now()
        ]);

        // Kirim email
        try {
            Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Kode OTP Reset Password');
            });

            return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email.']);
        }
    }
}