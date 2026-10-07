<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email - Invora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center font-sans">

    <div class="bg-white shadow-2xl rounded-xl p-8 md:p-10 w-full max-w-sm border border-gray-200"
         x-data="{ timer: 3600, get minutes() { return Math.floor(this.timer / 60) }, get seconds() { return this.timer % 60 }, get formatted() { return `${String(this.minutes).padStart(2,'0')}:${String(this.seconds).padStart(2,'0')}` } }"
         x-init="let interval = setInterval(() => { if (timer > 0) timer--; else clearInterval(interval) }, 1000)">

        <h1 class="text-3xl font-extrabold text-gray-800 text-center mb-3">Verifikasi Email</h1>
        <p class="text-center text-gray-500 mb-2">Masukkan kode 6 digit yang dikirim ke</p>
        <p class="text-center text-blue-600 font-semibold mb-8">{{ session('email') }}</p>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-2 rounded-lg mb-6 text-sm text-center">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.verify') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="email" value="{{ session('email') }}">

            <div>
                <label for="otp_code" class="block mb-2 text-sm font-medium text-gray-700 text-center">Kode Verifikasi</label>
                <input type="text" id="otp_code" name="otp_code" maxlength="6" required autofocus
                    class="w-full px-4 py-3 text-center text-2xl font-mono tracking-widest rounded-lg border @error('otp_code') border-red-500 @else border-gray-300 @enderror bg-white text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200"
                    placeholder="000000">
                @error('otp_code')
                    <p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-center">
                <span class="text-sm text-gray-500">Kode berlaku selama </span>
                <span class="text-sm font-semibold text-blue-600" x-text="formatted"></span>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-200 transition-all duration-300">
                Verifikasi
            </button>
        </form>

        <div class="mt-6 text-center space-y-3">
            <form method="POST" action="{{ route('register.resend-otp') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Kirim Ulang Kode OTP
                </button>
            </form>

            <div>
                <a href="{{ route('register') }}" class="text-sm text-gray-500 hover:text-gray-700">
                    Kembali ke halaman daftar
                </a>
            </div>
        </div>
    </div>

</body>

</html>
