{{-- resources/views/inventory-approval/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {!! session('warning') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="mb-3">
                    <i class="bi bi-clipboard-check"></i> Persetujuan Perhitungan SS & ROP
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Produk</a></li>
                        <li class="breadcrumb-item active">Persetujuan Perhitungan</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-warning border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-hourglass-split"></i> Menunggu Persetujuan
                        </h6>
                        <h3 class="mb-0 text-warning">{{ $requests->count() }}</h3>
                        <small class="text-muted">request pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-success border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-check-circle"></i> Disetujui Hari Ini
                        </h6>
                        <h3 class="mb-0 text-success">
                            {{ \App\Models\InventoryCalculationRequest::where('status', 'approved')->whereDate('approved_at', today())->count() }}
                        </h3>
                        <small class="text-muted">perhitungan diterapkan</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-danger border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">
                            <i class="bi bi-x-circle"></i> Ditolak Hari Ini
                        </h6>
                        <h3 class="mb-0 text-danger">
                            {{ \App\Models\InventoryCalculationRequest::where('status', 'rejected')->whereDate('approved_at', today())->count() }}
                        </h3>
                        <small class="text-muted">perhitungan tidak diterapkan</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check"></i> Daftar Perhitungan Pending
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ route('product.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali ke Inventory
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @if ($requests->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-2"><strong>Tidak ada perhitungan yang menunggu persetujuan</strong></p>
                        <p class="text-muted">Semua perhitungan SS & ROP sudah diproses</p>
                        <a href="{{ route('product.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left"></i> Kembali ke Inventory
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Produk</th>
                                    <th>Diminta Oleh</th>
                                    <th style="width: 150px;">Tanggal Request</th>
                                    <th class="text-center" style="width: 120px;">
                                        Avg Daily<br>
                                        <small class="text-muted">(Rata-rata Harian)</small>
                                    </th>
                                    <th class="text-center" style="width: 120px;">
                                        Max Daily<br>
                                        <small class="text-muted">(Maksimal Harian)</small>
                                    </th>
                                    <th class="text-center" style="width: 100px;">
                                        Safety Stock<br>
                                        <small class="text-muted">(SS)</small>
                                    </th>
                                    <th class="text-center" style="width: 100px;">
                                        Reorder Point<br>
                                        <small class="text-muted">(ROP)</small>
                                    </th>
                                    <th class="text-center" style="width: 180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $index => $request)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <div>
                                                    <strong>{{ $request->product->nama }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-box"></i> Stok: {{ $request->product->stok }}
                                                        @if ($request->product->rop > 0)
                                                            | ROP Lama: {{ $request->product->rop }}
                                                        @endif
                                                    </small>
                                                    <br>
                                                    <small
                                                        class="badge bg-{{ $request->product->stock_status == 'critical' ? 'danger' : ($request->product->stock_status == 'reorder' ? 'warning' : 'success') }}">
                                                        {{ ucfirst($request->product->stock_status) }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <i class="bi bi-person-circle"></i> {{ $request->requester->name ?? '-' }}
                                            <br>
                                            <small class="text-muted">{{ $request->requester->role ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <i class="bi bi-calendar3"></i> {{ $request->created_at->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> {{ $request->created_at->format('H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6">
                                                {{ number_format($request->avg_daily_usage, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark fs-6">
                                                {{ $request->max_daily_usage }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary fs-6">
                                                {{ $request->safety_stock }}
                                            </span>
                                            @if ($request->product->safety_stock > 0)
                                                <br>
                                                <small class="text-muted">
                                                    Lama: {{ $request->product->safety_stock }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark fs-6">
                                                {{ $request->rop }}
                                            </span>
                                            @if ($request->product->rop > 0)
                                                <br>
                                                <small class="text-muted">
                                                    Lama: {{ $request->product->rop }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm d-flex gap-1" role="group">
                                                <form action="{{ route('inventory-approval.approve', $request->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('✅ Setujui perhitungan ini?\n\nSS: {{ $request->safety_stock }} | ROP: {{ $request->rop }}\n\nNilai ini akan diterapkan ke produk.')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success"
                                                        title="Setujui Perhitungan">
                                                        <i class="bi bi-check-circle"></i> Setujui
                                                    </button>
                                                </form>
                                                <form action="{{ route('inventory-approval.reject', $request->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('❌ Tolak perhitungan ini?\n\nPerhitungan akan dibatalkan dan tidak diterapkan.')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger"
                                                        title="Tolak Perhitungan">
                                                        <i class="bi bi-x-circle"></i> Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Info Panel --}}
                    <div class="alert alert-info mt-4 mb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="alert-heading"><i class="bi bi-info-circle"></i> Informasi Perhitungan</h6>
                                <ul class="mb-0 small">
                                    <li><strong>Avg Daily Usage</strong>: Rata-rata penjualan harian berdasarkan data
                                        historis</li>
                                    <li><strong>Max Daily Usage</strong>: Penjualan maksimal dalam sehari</li>
                                    <li><strong>Safety Stock (SS)</strong>: Stok pengaman untuk mencegah kehabisan stok</li>
                                    <li><strong>Reorder Point (ROP)</strong>: Titik pemesanan ulang ketika stok mencapai
                                        level ini</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, .02);
            }

            .badge {
                font-weight: 500;
            }

            .btn-group-sm .btn {
                font-size: 0.875rem;
            }
        </style>
    @endpush
@endsection
