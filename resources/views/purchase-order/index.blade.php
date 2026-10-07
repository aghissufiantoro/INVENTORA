@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Daftar Purchase Order</h3>
            <a href="{{ route('purchase-order.create') }}" class="btn btn-primary">+ Buat PO Baru</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>Kode PO</th>
                            <th>Tanggal</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $po)
                            <tr>
                                <td>
                                    <strong>{{ $po->kode_po }}</strong>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($po->tanggal)->format('d M Y') }}</td>
                                <td>{{ $po->user->name }}</td>
                                <td>
                                    @if ($po->status === 'draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @else
                                        <span class="badge bg-success">Selesai</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('purchase-order.show', $po->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>

                                    @if (optional(auth()->user())->role === 'owner')
                                        @if ($po->status === 'draft')
                                            <a href="{{ route('purchase-order.receive', $po->id) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fas fa-box-open"></i> Terima Barang
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    <p>Belum ada Purchase Order</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
