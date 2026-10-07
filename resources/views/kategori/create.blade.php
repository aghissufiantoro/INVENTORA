@extends('layouts.app')
{{-- <script src="https://cdn.tailwindcss.com"></script> --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

@section('content')
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Tambah Kategori</h2>

        @if (session('success'))
            <div class="mb-4 text-green-600">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            <div class="mb-4 bg-gray-100 p-4 rounded">
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                <input type="text" name="nama" id="nama" class="w-full border-gray-300 rounded p-2 mt-1 bg-gray-200" required>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">
                Simpan
            </button>
        </form>
    </div>
@endsection
