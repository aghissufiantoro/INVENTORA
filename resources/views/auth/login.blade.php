<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Akun - Invora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center font-sans">

    <div class="bg-white shadow-2xl rounded-xl p-8 md:p-10 w-full max-w-sm border border-gray-200">

        <h1 class="text-3xl font-extrabold text-gray-800 text-center mb-3">Selamat Datang</h1>
        <p class="text-center text-gray-500 mb-8">Silakan masuk ke akun Anda</p>

        {{-- Menampilkan success message (setelah reset password) --}}
        @if (session('success'))
            <div
                class="bg-green-50 border border-green-200 text-green-600 px-4 py-2 rounded-lg mb-6 text-sm text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- Menampilkan error dari session --}}
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg mb-6 text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        {{-- Menampilkan error validasi dari withErrors() --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2 rounded-lg mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" x-data="{ show: false }" class="space-y-6">
            @csrf

            {{-- Username --}}
            <div>
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-4 py-2 rounded-lg border @error('username') border-red-500 @else border-gray-300 @enderror bg-white text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200"
                    placeholder="Masukkan username Anda">
                @error('username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password dengan show/hide --}}
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required
                        class="w-full px-4 py-2 rounded-lg border @error('password') border-red-500 @else border-gray-300 @enderror bg-white text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all duration-200 pr-10"
                        placeholder="Masukkan password Anda">

                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.964 9.964 0 012.042-3.362M9.88 9.88A3 3 0 0112 9c.512 0 1.005.128 1.437.354M15 12a3 3 0 01-3 3m0 0a3 3 0 01-3-3m6 0a3 3 0 00-3-3m9.193 9.193L4.807 4.807" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Link Lupa Password --}}
            <div class="mt-3 text-center">
                <a href="{{ route('password.request') }}" class="text-decoration-none">
                    Lupa Password?
                </a>
            </div>

            {{-- Tombol Login --}}
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-200 transition-all duration-300">
                Masuk
            </button>

        </form>
    </div>

</body>

</html>
