@extends('layouts.app')

@section('content')
    @if (Auth::user()->role === 'owner')
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Manajemen Akun</h1>
                <a href="{{ route('account.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    + Tambah Akun
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-200 text-green-700 p-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <table class="table-auto w-full border text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2 border">Nama</th>
                        <th class="px-4 py-2 border">Username</th>
                        <th class="px-4 py-2 border">Email</th>
                        <th class="px-4 py-2 border">Role</th>
                        <th class="px-4 py-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td class="border px-4 py-2">{{ $account->name }}</td>
                            <td class="border px-4 py-2">{{ $account->username }}</td>
                            <td class="border px-4 py-2">{{ $account->email }}</td>
                            <td class="border px-4 py-2 capitalize">{{ $account->role }}</td>
                            <td class="border px-4 py-2 space-x-2">
                                <a href="{{ route('account.edit', $account->id) }}"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">
                                    Edit
                                </a>
                                <form action="{{ route('account.destroy', $account->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Yakin hapus akun ini?')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-gray-500">Belum ada akun</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
