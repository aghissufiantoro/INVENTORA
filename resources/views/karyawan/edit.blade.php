@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Edit Karyawan</h2>

    <form action="{{ route('karyawan.update', $karyawan) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        @include('karyawan.form', ['karyawan' => $karyawan])
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        <a href="{{ route('karyawan.index') }}" class="text-gray-600 ml-2">Batal</a>
    </form>
@endsection
