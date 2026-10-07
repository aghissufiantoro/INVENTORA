@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-lg">
    <h1 class="text-2xl font-bold mb-4">Edit Akun</h1>

    <form action="{{ route('account.update', $account->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold mb-1">Nama</label>
            <input type="text" name="name" value="{{ $account->name }}" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Username</label>
            <input type="text" name="username" value="{{ $account->username }}" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Email</label>
            <input type="email" name="email" value="{{ $account->email }}" class="w-full border px-3 py-2 rounded" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Password <span class="text-gray-500 text-sm">(biarkan kosong jika tidak ingin diubah)</span></label>
            <input type="password" name="password" class="w-full border px-3 py-2 rounded">
        </div>
        <div>
            <label class="block font-semibold mb-1">Role</label>
            <select name="role" class="w-full border px-3 py-2 rounded">
                <option value="owner" {{ $account->role == 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="karyawan" {{ $account->role == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
            </select>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('account.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                Batal
            </a>
            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
