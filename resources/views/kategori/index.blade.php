@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Data Kategori</h2>
    <a href="{{ route('kategori.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">+ Tambah Kategori</a>
</div>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="overflow-x-auto bg-white shadow rounded">
    <table class="min-w-full border-collapse border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-200 px-4 py-2 text-left">No</th>
                <th class="border border-gray-200 px-4 py-2 text-left">Nama Kategori</th>
                <th class="border border-gray-200 px-4 py-2 text-left">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kategori as $index => $item)
                <tr class="hover:bg-gray-50">
                    <td class="border border-gray-200 px-4 py-2">{{ $index + 1 }}</td>
                    <td class="border border-gray-200 px-4 py-2">{{ $item->nama }}</td>
                    <td class="border border-gray-200 px-4 py-2 space-x-2">
                        <a href="{{ route('kategori.edit', $item->id) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('kategori.destroy', $item->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Yakin ingin hapus {{ $item->nama }}?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center p-4 text-gray-500">Belum ada kategori</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
