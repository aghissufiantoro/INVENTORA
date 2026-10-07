@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-4 text-secondary"><i class="bi bi-arrow-return-left"></i> Retur Barang</h4>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Form Retur --}}
        <div class="col-lg-4">
            <div class="card shadow-sm p-4 bg-light">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle"></i> Ajukan Retur</h5>
                <form method="POST" action="{{ route('retur.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->nama }} (stok: {{ $product->stok }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Retur</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea name="alasan" class="form-control" rows="3" required></textarea>
                    </div>

                    <button class="btn btn-secondary w-100">
                        <i class="bi bi-send"></i> Kirim Retur
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Retur --}}
        <div class="col-lg-8">
            <div class="card shadow-sm p-4 bg-light">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-task"></i> Daftar Retur</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>No</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Alasan</th>
                                <th>Diajukan Oleh</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($returs as $retur)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $retur->product->nama }}</td>
                                    <td>{{ $retur->jumlah }}</td>
                                    <td>{{ $retur->alasan }}</td>
                                    <td>{{ $retur->user->name }}</td>
                                    <td>
                                        @if($retur->status == 'menunggu')
                                            <span class="badge bg-warning text-dark">Menunggu</span>
                                        @elseif($retur->status == 'disetujui')
                                            <span class="badge bg-success">Disetujui</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(auth()->user()->role == 'owner' && $retur->status == 'menunggu')
                                            <form action="{{ route('retur.approve', $retur->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success"><i class="bi bi-check-circle"></i></button>
                                            </form>
                                            <form action="{{ route('retur.reject', $retur->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-danger"><i class="bi bi-x-circle"></i></button>
                                            </form>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data retur</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body { background-color: #f1f1f1; }
.table td, .table th { vertical-align: middle; }
@media (max-width: 992px) {
    .col-lg-4, .col-lg-8 { flex: 100%; max-width: 100%; }
}
</style>
@endsection
