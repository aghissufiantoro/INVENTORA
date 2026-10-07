@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-4">Edit Kategori</h2>

    <form action="{{ route('kategori.update', $kategori->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block mb-1">Nama Kategori</label>
            <input type="text" name="nama" value="{{ $kategori->nama }}" class="w-full border rounded p-2" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('kategori.index') }}" class="ml-2 text-gray-600">Batal</a>
    </form>
</div>
@endsection
