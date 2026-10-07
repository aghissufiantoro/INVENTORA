@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="fw-bold mb-3">Edit Kunjungan Sales</h3>

        <form action="{{ route('sales.update', $sales->id) }}" method="POST" class="card p-4 shadow-sm">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Sales *</label>
                    <input type="text" name="nama_sales" value="{{ old('nama_sales', $sales->nama_sales) }}"
                        class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Perusahaan</label>
                    <input type="text" name="perusahaan" value="{{ old('perusahaan', $sales->perusahaan) }}"
                        class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tanggal Kunjungan *</label>
                    <input type="date" name="tanggal_kunjungan"
                        value="{{ old('tanggal_kunjungan', $sales->tanggal_kunjungan) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kontak Sales</label>
                    <input type="text" name="kontak_sales" value="{{ old('kontak_sales', $sales->kontak_sales) }}"
                        class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Tujuan Kunjungan</label>
                <input type="text" name="tujuan" value="{{ old('tujuan', $sales->tujuan) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Hasil Kunjungan</label>
                <textarea name="hasil_kunjungan" class="form-control" rows="4">{{ old('hasil_kunjungan', $sales->hasil_kunjungan) }}</textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
@endsection
