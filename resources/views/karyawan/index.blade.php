@extends('layouts.app')

@section('content')
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Manajemen Karyawan</h1>

        <a href="{{ route('karyawan.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">+ Tambah Karyawan</a>

        @if (session('success'))
            <div class="bg-green-200 text-green-700 p-2 mt-2 rounded">
                {{ session('success') }}
            </div>
        @endif

        <table class="table-auto w-full mt-4 border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border">Nama</th>
                    <th class="px-4 py-2 border">No HP</th>
                    <th class="px-4 py-2 border">Alamat</th>
                    <th class="px-4 py-2 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($karyawans as $karyawan)
                    <tr>
                        <td class="border px-4 py-2">{{ $karyawan->nama }}</td>
                        <td class="border px-4 py-2">{{ $karyawan->no_hp }}</td>
                        <td class="border px-4 py-2">{{ $karyawan->alamat }}</td>
                        <td class="border px-4 py-2 text-center space-x-1">
                            <a href="{{ route('karyawan.edit', $karyawan) }}"
                                class="bg-blue-500 text-white px-2 py-1 rounded">Edit</a>
                            <form action="{{ route('karyawan.destroy', $karyawan) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin hapus?')"
                                    class="bg-red-500 text-white px-2 py-1 rounded">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
