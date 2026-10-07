@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Tambah Karyawan</h2>

    <form action="{{ route('karyawan.store') }}" method="POST" class="space-y-4">
        @csrf
        @include('karyawan.form')

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
        <a href="{{ route('karyawan.index') }}" class="text-gray-600 ml-2">Batal</a>
    </form>
@endsection
