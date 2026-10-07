{{-- resources/views/product/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Detail Product</h2>
                    <a href="{{ route('product.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                {{-- Alert Status --}}
                @if ($product->needsReorder())
                    <div class="alert alert-{{ $product->stock_status == 'critical' ? 'danger' : 'warning' }} alert-dismissible fade show"
                        role="alert">
                        <h5 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            {{ $product->stock_status == 'critical' ? 'CRITICAL: Stok Hampir Habis!' : 'PERINGATAN: Perlu Reorder!' }}
                        </h5>
                        <p class="mb-0">
                            Product ini telah mencapai
                            {{ $product->stock_status == 'critical' ? 'level kritis' : 'reorder point' }}.
                            Segera lakukan pemesanan ke supplier untuk menghindari stockout.
                        </p>
                        <hr>
                        <p class="mb-0">
                            <strong>Rekomendasi Order:</strong> {{ $info['recommended_order_qty'] }} unit
                        </p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    {{-- Product Info Card --}}
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Informasi Product</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    {{-- <tr>
                                        <td width="200" class="fw-bold">Kode Product</td>
                                        <td>: <code>{{ $product->kode_product ?? '-' }}</code></td>
                                    </tr> --}}
                                    <tr>
                                        <td class="fw-bold">Nama Product</td>
                                        <td>: <strong class="fs-5">{{ $product->nama }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Kategori</td>
                                        <td>: <span class="badge bg-info">{{ $product->kategori->nama ?? '-' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Harga</td>
                                        <td>: <strong class="text-success fs-5">Rp
                                                {{ number_format($product->harga, 0, ',', '.') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Nilai Stok</td>
                                        <td>: Rp {{ number_format($info['stock_value'], 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        {{-- Inventory Parameters --}}
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-gear"></i> Parameter Inventory Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Safety Stock</label>
                                            <h4><span class="badge bg-secondary">{{ $product->safety_stock }} unit</span>
                                            </h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Reorder Point (ROP)</label>
                                            <h4><span class="badge bg-dark">{{ $product->rop }} unit</span></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Lead Time</label>
                                            <h5>{{ $product->lead_time_days }} hari</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Avg. Penjualan Harian</label>
                                            <h5>{{ $product->avg_daily_usage }} unit/hari</h5>
                                        </div>
                                    </div>
                                    @if ($product->max_daily_usage)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="text-muted small">Max. Penjualan Harian</label>
                                                <h5>{{ $product->max_daily_usage }} unit/hari</h5>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($product->maximum_stock)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="text-muted small">Maximum Stock</label>
                                                <h5>{{ $product->maximum_stock }} unit</h5>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <hr>

                                <div class="alert alert-info mb-0">
                                    <strong><i class="bi bi-info-circle"></i> Perhitungan ROP:</strong><br>
                                    ROP = ({{ $product->avg_daily_usage }} × {{ $product->lead_time_days }}) +
                                    {{ $product->safety_stock }} = <strong>{{ $product->rop }} unit</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Stock Status Card --}}
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-{{ $product->status_color }}">
                                <h5 class="mb-0 text-white"><i class="bi bi-bar-chart-fill"></i> Status Stok</h5>
                            </div>
                            <div class="card-body text-center">
                                <h1 class="display-3 mb-3 text-{{ $product->status_color }}">{{ $product->stok }}</h1>
                                <p class="text-muted mb-3">Unit Tersedia</p>
                                {!! $product->status_badge !!}

                                {{-- Stock Level Indicator --}}
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small>Safety Stock</small>
                                        <small>ROP</small>
                                    </div>
                                    <div class="progress" style="height: 30px;">
                                        @php
                                            $percentage =
                                                $product->rop > 0
                                                    ? min(100, ($product->stok / $product->rop) * 100)
                                                    : 0;
                                            $barColor =
                                                $percentage <= 50
                                                    ? 'danger'
                                                    : ($percentage <= 100
                                                        ? 'warning'
                                                        : 'success');
                                        @endphp
                                        <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                                            style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            {{ round($percentage) }}%
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-muted">{{ $product->safety_stock }}</small>
                                        <small class="text-muted">{{ $product->rop }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Analytics Card --}}
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Analisis Stok</h6>
                            </div>
                            <div class="card-body">

                                <div class="mb-3">
                                    <label class="text-muted small">Rekomendasi Order</label>
                                    <h4 class="mb-0">
                                        <span class="badge bg-primary">{{ $info['recommended_order_qty'] }} unit</span>
                                    </h4>
                                    <small class="text-muted">
                                        @if ($product->maximum_stock)
                                            Untuk mencapai maximum stock
                                        @else
                                            2x dari ROP
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        @push('scripts')
                            <script>
                                async function calculateAuto() {
                                    try {
                                        const response = await fetch("{{ route('product.auto-recommendation', $product->id) }}");
                                        const data = await response.json();

                                        if (data.success) {
                                            document.getElementById('autoSS').textContent = data.safety_stock;
                                            document.getElementById('autoROP').textContent = data.reorder_point;
                                            document.getElementById('autoResult').style.display = 'block';
                                        } else {
                                            alert(data.error);
                                        }
                                    } catch (error) {
                                        alert('Gagal menghitung rekomendasi');
                                    }
                                }
                            </script>
                        @endpush

                        {{-- Action Buttons --}}
                        <div class="d-grid gap-2">
                            <a href="{{ route('product.edit', $product->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit Product
                            </a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#adjustStockModal">
                                <i class="bi bi-arrow-left-right"></i> Adjust Stock
                            </button>
                            <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus product ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash"></i> Hapus Product
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Adjust Stock --}}
    <div class="modal fade" id="adjustStockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Adjust Stock: {{ $product->nama }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('product.adjust-stock', $product->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Stok Saat Ini:</strong> {{ $product->stok }} unit
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipe Adjustment</label>
                            <select name="type" class="form-select" required>
                                <option value="add">➕ Tambah Stok (Receiving/Pembelian)</option>
                                <option value="subtract">➖ Kurangi Stok (Adjustment/Retur/Kerusakan)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah</label>
                            <input type="number" name="quantity" class="form-control" required min="1"
                                placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Misal: Receiving dari PO #123, Retur ke supplier, dll"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
