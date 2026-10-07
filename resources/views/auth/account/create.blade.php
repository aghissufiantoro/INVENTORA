@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Tambah Akun</h1>

    <form action="{{ route('account.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">Nama</label>
            <input type="text" name="name" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Username</label>
            <input type="text" name="username" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Email</label>
            <input type="email" name="email" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Password</label>
            <input type="password" name="password" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Role</label>
            <select name="role" class="w-full border px-3 py-2 rounded">
                <option value="owner">Owner</option>
                <option value="karyawan">Karyawan</option>
            </select>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('account.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
