@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h3 class="mb-4 text-secondary fw-bold">Detail Purchase Order</h3>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Kode PO:</strong> {{ $purchaseOrder->kode_po }}</p>
                        <p><strong>Tanggal:</strong> {{ $purchaseOrder->tanggal }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dibuat Oleh:</strong> {{ $purchaseOrder->user->name ?? 'Tidak diketahui' }}</p>
                        <p><strong>Status:</strong>
                            <span
                                class="badge bg-{{ $purchaseOrder->status === 'disetujui' ? 'success' : ($purchaseOrder->status === 'selesai' ? 'primary' : 'secondary') }}">
                                {{ ucfirst($purchaseOrder->status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <hr>

                <h5 class="fw-semibold mb-3">Daftar Barang</h5>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchaseOrder->details as $detail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $detail->product->nama ?? '-' }}</td>
                                    <td>{{ $detail->jumlah }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada barang ditambahkan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('purchase-order.pdf', $purchaseOrder) }}" class="btn btn-danger">
                        <i class="bi bi-filetype-pdf"></i> Cetak PDF
                    </a>

                </div>
            </div>
        </div>
    </div>
@endsection
