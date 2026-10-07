@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Kunjungan Sales</h3>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kunjungan
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Sales</th>
                            <th>Perusahaan</th>
                            <th>Tanggal</th>
                            <th>Tujuan</th>
                            <th>Hasil Kunjungan</th>
                            <th>Kontak</th>
                            <th>Dicatat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $index => $sale)
                            <tr>
                                <td>{{ $index + $sales->firstItem() }}</td>
                                <td>{{ $sale->nama_sales }}</td>
                                <td>{{ $sale->perusahaan ?? '-' }}</td>
                                <td>{{ $sale->tanggal_kunjungan }}</td>
                                <td>{{ $sale->tujuan ?? '-' }}</td>
                                <td>{{ Str::limit($sale->hasil_kunjungan, 40) }}</td>
                                <td>{{ $sale->kontak_sales ?? '-' }}</td>
                                <td>{{ $sale->user->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data kunjungan sales</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $sales->links() }}
            </div>
        </div>
    </div>
@endsection
