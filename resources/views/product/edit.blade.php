@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                {{-- CARD 1: Edit Informasi Produk --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Informasi Product</h4>
                    </div>
                    <div class="card-body">
                        {{-- Current Stock Info --}}
                        <div
                            class="alert alert-{{ $product->stock_status == 'critical' ? 'danger' : ($product->stock_status == 'reorder' ? 'warning' : 'info') }} mb-4">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <strong>Status Stok Saat Ini:</strong><br>
                                    {!! $product->status_badge !!}
                                </div>
                                <div class="col-md-3">
                                    <strong>Stok:</strong> {{ $product->stok }} unit
                                </div>
                                <div class="col-md-3">
                                    <strong>ROP:</strong> {{ $product->rop }} unit
                                </div>
                                <div class="col-md-3">
                                    <strong>Safety Stock:</strong> {{ $product->safety_stock }} unit
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('product.update', $product->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nama Product <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nama"
                                            class="form-control @error('nama') is-invalid @enderror"
                                            value="{{ old('nama', $product->nama) }}" required>
                                        @error('nama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Kategori <span
                                                class="text-danger">*</span></label>
                                        <select name="kategori_id"
                                            class="form-select @error('kategori_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategori as $kat)
                                                <option value="{{ $kat->id }}"
                                                    {{ old('kategori_id', $product->kategori_id) == $kat->id ? 'selected' : '' }}>
                                                    {{ $kat->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('kategori_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Harga <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="harga"
                                                class="form-control @error('harga') is-invalid @enderror"
                                                value="{{ old('harga', $product->harga) }}" required min="0">
                                        </div>
                                        @error('harga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Stok <span class="text-danger">*</span></label>
                                        <input type="number" name="stok"
                                            class="form-control @error('stok') is-invalid @enderror"
                                            value="{{ old('stok', $product->stok) }}" required min="0">
                                        <small class="text-muted">Untuk adjust stok besar, gunakan tombol "Adjust Stock" di
                                            halaman index</small>
                                        @error('stok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <a href="{{ route('product.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- CARD 2: Informasi Inventory Saat Ini --}}
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Pengaturan Inventory Management</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Parameter Saat Ini:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Safety Stock</label>
                                    <h4><span class="badge bg-secondary">{{ $product->safety_stock }} unit</span></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Reorder Point (ROP)</label>
                                    <h4><span class="badge bg-dark">{{ $product->rop }} unit</span></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Lead Time</label>
                                    <h4>{{ $product->lead_time_days }} hari</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Avg. Penjualan Harian</label>
                                    <h4>{{ $product->avg_daily_usage }} unit/hari</h4>
                                </div>
                            </div>
                            @if ($product->max_daily_usage)
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="text-muted small">Max. Penjualan Harian</label>
                                        <h4>{{ $product->max_daily_usage }} unit/hari</h4>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="alert alert-info mb-3">
                            <strong><i class="bi bi-info-circle"></i> Perhitungan ROP Saat Ini:</strong><br>
                            ROP = ({{ $product->avg_daily_usage }} × {{ $product->lead_time_days }}) +
                            {{ $product->safety_stock }} = <strong>{{ $product->rop }} unit</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR: Auto Calculate --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0 mb-4">
                    {{-- Area untuk menampilkan rekomendasi --}}
                    <div id="recommendationResult" style="display:none;">
                        <div class="alert alert-success">
                            <h6 class="alert-heading"><i class="bi bi-check-circle"></i> Hasil Perhitungan</h6>
                            <hr>

                            {{-- Metrics Utama --}}
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Max Daily Sales:</strong><br>
                                    <span id="recMaxSales" class="badge bg-danger fs-5">-</span> pcs
                                </div>
                                <div class="col-6">
                                    <strong>Avg Daily Sales:</strong><br>
                                    <span id="recAvgSales" class="badge bg-info fs-5">-</span> pcs
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Safety Stock:</strong><br>
                                    <span id="recSafetyStock" class="badge bg-primary fs-5">-</span> unit
                                </div>
                                <div class="col-6">
                                    <strong>Reorder Point:</strong><br>
                                    <span id="recROP" class="badge bg-success fs-5">-</span> unit
                                </div>
                            </div>

                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Data:</strong> <span id="recDataPoints">-</span> hari aktif<br>
                                    <strong>Lead Time:</strong> <span id="recLeadTime">-</span> hari<br>
                                    <strong>Total Terjual:</strong> <span id="recTotalSold">-</span> pcs
                                </small>
                            </div>
                        </div>

                        {{-- Detail Perhitungan Step-by-Step --}}
                        <div class="alert alert-light">
                            <strong>📐 Langkah Perhitungan:</strong>
                            <ol id="calculationSteps" class="mb-0 mt-2 small">
                                <!-- Will be filled by JavaScript -->
                            </ol>
                        </div>

                        {{-- Data Penjualan Harian --}}
                        <details class="mb-3">
                            <summary class="text-primary fw-bold" style="cursor: pointer;">
                                📊 Lihat Data Penjualan Harian
                            </summary>
                            <div class="alert alert-secondary mt-2 mb-0">
                                <small>
                                    <strong>Penjualan per hari:</strong><br>
                                    <code id="dailySalesArray" style="word-break: break-all; font-size: 0.85em;">-</code>
                                </small>
                            </div>
                        </details>

                        <form action="{{ route('product.apply-auto-recommendation', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-check-circle"></i> Terapkan Rekomendasi
                            </button>
                        </form>
                        <button type="button" class="btn btn-secondary w-100 btn-sm"
                            onclick="document.getElementById('recommendationResult').style.display='none'">
                            Sembunyikan
                        </button>
                    </div>

                    <div id="recommendationError" class="alert alert-warning" style="display:none;">
                        <i class="bi bi-exclamation-triangle"></i> <span id="errorMessage"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            // Fetch Recommendation dengan detail lengkap
            document.getElementById('fetchRecommendation').addEventListener('click', async function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghitung...';

                document.getElementById('recommendationResult').style.display = 'none';
                document.getElementById('recommendationError').style.display = 'none';

                try {
                    const response = await fetch("{{ route('product.auto-recommendation', $product->id) }}");
                    const data = await response.json();

                    // Log untuk debugging di console browser
                    console.log('=== API Response ===');
                    console.log(data);

                    if (data.success) {
                        // Tampilkan metrics utama
                        document.getElementById('recMaxSales').textContent = data.max_daily_sales;
                        document.getElementById('recAvgSales').textContent = data.avg_daily_sales.toFixed(2);
                        document.getElementById('recSafetyStock').textContent = data.safety_stock;
                        document.getElementById('recROP').textContent = data.reorder_point;
                        document.getElementById('recDataPoints').textContent = data.active_days;
                        document.getElementById('recLeadTime').textContent = data.lead_time;
                        document.getElementById('recTotalSold').textContent = data.total_sold;

                        // Tampilkan data penjualan harian sebagai array
                        if (data.daily_sales_array) {
                            document.getElementById('dailySalesArray').textContent =
                                JSON.stringify(data.daily_sales_array, null, 2);
                        }

                        // Tampilkan step-by-step calculation
                        let stepsHTML = '';
                        if (data.calculation_steps) {
                            Object.values(data.calculation_steps).forEach((step, index) => {
                                stepsHTML += `<li>${step}</li>`;
                            });
                            document.getElementById('calculationSteps').innerHTML = stepsHTML;
                        }

                        // Tampilkan hasil
                        document.getElementById('recommendationResult').style.display = 'block';

                        // Scroll ke hasil
                        document.getElementById('recommendationResult').scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });

                    } else {
                        // Tampilkan error dengan detail
                        let errorMsg = data.error || 'Error tidak diketahui';
                        if (data.message) {
                            errorMsg += '<br><small>' + data.message + '</small>';
                        }
                        if (data.active_days !== undefined) {
                            errorMsg += '<br><small>Data aktif: ' + data.active_days + ' hari</small>';
                        }

                        document.getElementById('errorMessage').innerHTML = errorMsg;
                        document.getElementById('recommendationError').style.display = 'block';

                        console.error('Calculation failed:', data);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    document.getElementById('errorMessage').innerHTML =
                        'Terjadi kesalahan saat menghitung rekomendasi.<br><small>' + error.message + '</small>';
                    document.getElementById('recommendationError').style.display = 'block';
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-calculator"></i> Hitung Rekomendasi';
                }
            });
        </script>
    @endpush
@endsection
