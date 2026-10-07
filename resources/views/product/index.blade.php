{{-- resources/views/product/index.blade.php --}}
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

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <h2 class="mb-3">Inventory Management</h2>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Produk</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm border-start border-success border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Stok Normal</h6>
                        <h3 class="mb-0 text-success">{{ $stats['normal'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm border-start border-warning border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Stok Rendah</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['low'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm border-start border-warning border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Stok Pemesanan Ulang</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['reorder'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border-0 shadow-sm border-start border-danger border-3">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Stok Kritikal</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['critical'] }}</h3>
                    </div>
                </div>
            </div>
            @if (Auth::user()->role === 'owner')
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body">
                            <h6 class="mb-2">Total Pendapatan</h6>
                            <h5 class="mb-0">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</h5>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- 🆕 Control Panel SS & ROP (Di atas tabel, bukan di dalam tabel) --}}
        @if (auth()->user()->role === 'karyawan')
            <div class="card shadow-sm border-0 mb-3 bg-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0">
                                <i class="bi bi-calculator"></i> Perhitungan Safety Stock & Reorder Point
                            </h6>
                            <small class="text-muted">Hitung otomatis berdasarkan data penjualan historis</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <form action="{{ route('product.auto-calculate-all') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-calculator"></i> Hitung Semua SS & ROP
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (auth()->user()->role === 'owner')
            <div class="card shadow-sm border-0 mb-3 bg-warning bg-opacity-10 border-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0">
                                <i class="bi bi-clipboard-check"></i> Persetujuan Perhitungan
                            </h6>
                            <small class="text-muted">Tinjau dan setujui perhitungan SS & ROP dari karyawan</small>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('inventory-approval.index') }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-bell"></i> Lihat Request
                                @php
                                    $pendingCount = App\Models\InventoryCalculationRequest::where(
                                        'status',
                                        'pending',
                                    )->count();
                                @endphp
                                @if ($pendingCount > 0)
                                    <span class="badge bg-danger">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Daftar produk</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ route('product.reorder-alert') }}" class="btn btn-warning btn-sm me-2">
                            <i class="bi bi-exclamation-triangle"></i> Waktunya Kulak
                            @if ($stats['reorder'] + $stats['critical'] > 0)
                                <span class="badge bg-danger">{{ $stats['reorder'] + $stats['critical'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('product.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Tambah produk
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                {{-- Filter & Search --}}
                <form method="GET" action="{{ route('product.index') }}" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama produk"
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="kategori_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategori as $kat)
                                    <option value="{{ $kat->id }}"
                                        {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal
                                </option>
                                <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Low Stock
                                </option>
                                <option value="reorder" {{ request('status') == 'reorder' ? 'selected' : '' }}>Reorder
                                    Point</option>
                                <option value="critical" {{ request('status') == 'critical' ? 'selected' : '' }}>Critical
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Product</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th class="text-center">Stok</th>
                                <th class="text-center">Safety Stock</th>
                                <th class="text-center">ROP</th>
                                <th class="text-center">Status Stok</th>
                                <th class="text-center">Status SS & ROP</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $index => $product)
                                <tr
                                    class="{{ $product->stock_status == 'critical' ? 'table-danger' : ($product->stock_status == 'reorder' ? 'table-warning' : '') }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <strong>{{ $product->nama }}</strong>
                                                @if ($product->needsReorder())
                                                    <br><small class="text-danger">⚠️ Perlu Reorder!</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $product->kategori->nama ?? '-' }}</td>
                                    <td>
                                        Rp {{ number_format($product->harga, 0, ',', '.') }}
                                        @if ($product->harga_beli > 0 && $product->harga <= $product->harga_beli)
                                            <span class="ms-2" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Harga jual lebih rendah dari harga beli">
                                                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $product->stock_status == 'critical' ? 'danger' : ($product->stock_status == 'reorder' ? 'warning' : 'info') }} fs-6">
                                            {{ $product->stok }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $product->safety_stock }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark">{{ $product->rop }}</span>
                                    </td>
                                    <td class="text-center">{!! $product->status_badge !!}</td>
                                    <td class="text-center">
                                        @if ($product->pendingCalculation)
                                            <span class="badge bg-warning">Belum Diapprove</span>
                                        @else
                                            <span class="badge bg-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('product.show', $product->id) }}" class="btn btn-info"
                                                title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('product.edit', $product->id) }}" class="btn btn-warning"
                                                title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#adjustStockModal{{ $product->id }}"
                                                title="Adjust Stock">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </button>
                                            <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus product ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Modal Adjust Stock --}}
                                        <div class="modal fade" id="adjustStockModal{{ $product->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Adjust Stock: {{ $product->nama }}</h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('product.adjust-stock', $product->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-info">
                                                                <strong>Stok Saat Ini:</strong> {{ $product->stok }} unit
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Tipe Adjustment</label>
                                                                <select name="type" class="form-select" required>
                                                                    <option value="add">Tambah Stok (Receiving)</option>
                                                                    <option value="subtract">Kurangi Stok
                                                                        (Adjustment/Retur)
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Jumlah</label>
                                                                <input type="number" name="quantity"
                                                                    class="form-control" required min="1">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Catatan (Opsional)</label>
                                                                <textarea name="notes" class="form-control" rows="2"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Belum ada product</p>
                                        <a href="{{ route('product.create') }}" class="btn btn-primary">Tambah Product
                                            Pertama</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        </style>
    @endpush
@endsection
