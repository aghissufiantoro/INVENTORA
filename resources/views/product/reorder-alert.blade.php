{{-- resources/views/product/reorder-alert.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-exclamation-triangle-fill text-warning"></i> Reorder Alert</h2>
                    <a href="{{ route('product.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Product
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-danger border-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Critical Stock</h6>
                        <h2 class="mb-0 text-danger">
                            {{ $stats['critical'] }}
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </h2>
                        <small class="text-muted">Di bawah safety stock</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">At Reorder Point</h6>
                        <h2 class="mb-0 text-warning">
                            {{ $stats['reorder'] }}
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </h2>
                        <small class="text-muted">Perlu segera di-reorder</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Low Stock</h6>
                        <h2 class="mb-0 text-warning">
                            {{ $stats['low'] }}
                            <i class="bi bi-dash-circle-fill"></i>
                        </h2>
                        <small class="text-muted">Mendekati reorder point</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Daftar Product yang Perlu Reorder
                    <span class="badge bg-dark float-end">{{ $products->count() }} Products</span>
                </h5>
            </div>
            <div class="card-body">
                @if ($products->count() > 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle-fill"></i>
                        <strong>Perhatian:</strong> Product berikut memerlukan tindakan segera. Silakan lakukan pemesanan ke
                        supplier untuk menghindari kehabisan stok.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Product</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Stok Saat Ini</th>
                                    <th class="text-center">Safety Stock</th>
                                    <th class="text-center">ROP</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Estimasi Habis</th>
                                    <th class="text-center">Rekomendasi Order</th>
                                    <th class="text-center">Estimasi Biaya</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $index => $product)
                                    @php
                                        $daysUntilStockout =
                                            $product->avg_daily_usage > 0
                                                ? round($product->stok / $product->avg_daily_usage, 1)
                                                : null;
                                        $recommendedOrder = $product->maximum_stock
                                            ? max(0, $product->maximum_stock - $product->stok)
                                            : $product->rop * 2;
                                        $estimasiBiaya = $recommendedOrder * ($product->harga_beli ?? 0);
                                    @endphp
                                    <tr
                                        class="{{ $product->stock_status == 'critical' ? 'table-danger' : 'table-warning' }}">
                                        <td>{{ $index + 1 }}</td>
                                        {{-- <td>
                                            <strong>{{ $product->nama }}</strong>
                                            @if ($product->kode_product)
                                                <br><small
                                                    class="text-muted"><code>{{ $product->kode_product }}</code></small>
                                            @endif
                                        </td> --}}
                                        <td class="text-left">
                                            <span>{{ $product->nama }}</span>
                                        </td>
                                        <td>{{ $product->kategori->nama ?? '-' }}</td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-{{ $product->stock_status == 'critical' ? 'danger' : 'warning' }} fs-6">
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
                                            @if ($daysUntilStockout)
                                                <span
                                                    class="badge bg-{{ $daysUntilStockout <= 3 ? 'danger' : 'warning' }}">
                                                    {{ $daysUntilStockout }} hari
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $recommendedOrder }} unit</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($product->harga_beli > 0)
                                                <strong
                                                    class="text-success">Rp{{ number_format($estimasiBiaya, 0, ',', '.') }}</strong>
                                                <br><small
                                                    class="text-muted">@Rp{{ number_format($product->harga_beli, 0, ',', '.') }}/unit</small>
                                            @else
                                                <span class="badge bg-secondary">Harga belum ada</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('product.show', $product->id) }}" class="btn btn-info"
                                                    title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#orderModal{{ $product->id }}" title="Create Order">
                                                    <i class="bi bi-cart-plus"></i>
                                                </button>
                                            </div>

                                            {{-- Modal Create Purchase Order --}}
                                            <div class="modal fade" id="orderModal{{ $product->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">Create Purchase Order</h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h6 class="mb-3">{{ $product->nama }}</h6>

                                                            <div class="alert alert-info">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <strong>Stok Saat Ini:</strong><br>
                                                                        {{ $product->stok }} unit
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <strong>ROP:</strong><br>
                                                                        {{ $product->rop }} unit
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="alert alert-success">
                                                                <i class="bi bi-lightbulb-fill"></i>
                                                                <strong>Rekomendasi:</strong><br>
                                                                Order <strong>{{ $recommendedOrder }} unit</strong>
                                                                @if ($product->maximum_stock)
                                                                    untuk mencapai maximum stock
                                                                    ({{ $product->maximum_stock }} unit)
                                                                @else
                                                                    (2x dari ROP)
                                                                @endif
                                                                @if ($product->harga_beli > 0)
                                                                    <hr>
                                                                    <strong>Estimasi Biaya:</strong><br>
                                                                    Rp{{ number_format($estimasiBiaya, 0, ',', '.') }}
                                                                    <br><small class="text-muted">({{ $recommendedOrder }}
                                                                        unit ×
                                                                        Rp{{ number_format($product->harga_beli, 0, ',', '.') }})</small>
                                                                @endif
                                                            </div>

                                                            {{-- Jika kamu punya sistem Purchase Order, form nya bisa di sini --}}
                                                            <div class="text-center">
                                                                <a href="{{ route('purchase-order.create') }}"
                                                                    class="btn btn-primary">Buat Purchase Order</a>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Tutup</button>
                                                            {{-- <button type="submit" class="btn btn-success">Create PO</button> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary Card --}}
                    <div class="card bg-light mt-4">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-clipboard-check"></i> Ringkasan Reorder</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Products Perlu Reorder:</strong><br>
                                    <span class="fs-4 text-primary">{{ $products->count() }} items</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Rekomendasi Order:</strong><br>
                                    @php
                                        $totalOrder = $products->sum(function ($p) {
                                            return $p->maximum_stock
                                                ? max(0, $p->maximum_stock - $p->stok)
                                                : $p->rop * 2;
                                        });
                                    @endphp
                                    <span class="fs-4 text-success">{{ $totalOrder }} units</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Estimasi Total Biaya Pembelian:</strong><br>
                                    @php
                                        $totalValue = $products->sum(function ($p) {
                                            $orderQty = $p->maximum_stock
                                                ? max(0, $p->maximum_stock - $p->stok)
                                                : $p->rop * 2;
                                            return $orderQty * ($p->harga_beli ?? 0);
                                        });
                                    @endphp
                                    <span class="fs-5 text-info">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
                                    <br><small class="text-muted">*Berdasarkan harga beli terakhir</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                        <h3 class="mt-3 text-success">Semua Stok Aman!</h3>
                        <p class="text-muted">Tidak ada product yang memerlukan reorder saat ini.</p>
                        <a href="{{ route('product.index') }}" class="btn btn-primary mt-3">
                            <i class="bi bi-box-seam"></i> Lihat Semua Product
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, .05);
            }
        </style>
    @endpush
@endsection
