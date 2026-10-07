<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'karyawan',
        ]);

        $otp = rand(100000, 999999);
        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'otp_code' => $otp,
            'created_at' => Carbon::now(),
        ]);

        try {
            Mail::send('emails.verify-email', ['otp' => $otp, 'userName' => $request->name], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Verifikasi Email - Invora System');
            });

            return redirect()->route('register.verify.form')
                ->with('email', $request->email)
                ->with('success', 'Akun berhasil dibuat! Kode verifikasi telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            return redirect()->route('register.verify.form')
                ->with('email', $request->email)
                ->with('success', 'Akun berhasil dibuat! Silakan masukkan kode verifikasi yang dikirim ke email Anda.');
        }
    }

    public function showVerifyForm()
    {
        if (!session('email')) {
            return redirect()->route('register');
        }
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|digits:6',
        ], [
            'otp_code.required' => 'Kode verifikasi wajib diisi.',
            'otp_code.digits' => 'Kode verifikasi harus 6 digit.',
        ]);

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['otp_code' => 'Kode verifikasi tidak valid.']);
        }

        $createdAt = Carbon::parse($passwordReset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['otp_code' => 'Kode verifikasi telah kadaluarsa. Silakan minta kode baru.']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Email berhasil diverifikasi! Silakan login dengan akun Anda.');
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $otp = rand(100000, 999999);
        $token = Str::random(60);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'otp_code' => $otp,
            'created_at' => Carbon::now(),
        ]);

        try {
            Mail::send('emails.verify-email', ['otp' => $otp, 'userName' => $user->name], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Verifikasi Email - Invora System');
            });

            return back()->with('success', 'Kode verifikasi baru telah dikirim ke email Anda!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email. Pastikan konfigurasi SMTP sudah benar.']);
        }
    }
}
