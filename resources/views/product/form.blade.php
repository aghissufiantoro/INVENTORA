{{-- resources/views/product/create.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Tambah Product Baru</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('product.store') }}" method="POST">
                            @csrf

                            {{-- Informasi Dasar --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Product</label>
                                        <input type="text" name="nama"
                                            class="form-control @error('nama') is-invalid @enderror"
                                            value="{{ old('nama') }}" required>
                                        @error('nama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Kategori</label>
                                        <select name="kategori_id"
                                            class="form-control @error('kategori_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach ($kategori as $kat)
                                                <option value="{{ $kat->id }}"
                                                    {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
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
                                        <label class="form-label">Harga</label>
                                        <input type="number" name="harga"
                                            class="form-control @error('harga') is-invalid @enderror"
                                            value="{{ old('harga') }}" required min="0">
                                        @error('harga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stok Awal</label>
                                        <input type="number" name="stok"
                                            class="form-control @error('stok') is-invalid @enderror"
                                            value="{{ old('stok', 0) }}" required min="0">
                                        @error('stok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Lead Time
                                        <small class="text-muted">(Hari)</small>
                                    </label>
                                    <input type="number" name="lead_time_days" id="lead_time"
                                        class="form-control @error('lead_time_days') is-invalid @enderror"
                                        value="{{ old('lead_time_days', 3) }}" required min="1">
                                    <small class="form-text text-muted">Waktu tunggu dari supplier</small>
                                    @error('lead_time_days')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                {{-- ROP Calculator Preview --}}
                                <div class="alert alert-info">
                                    <strong>🔔 Reorder Point (ROP) akan otomatis dihitung:</strong><br>
                                    <span id="rop-preview">ROP = (5 × 3) + 10 = <strong>25 unit</strong></span>
                                    <br><small>Formula: (Avg Daily Usage × Lead Time) + Safety Stock</small>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Simpan Product</button>
                                    <a href="{{ route('product.index') }}" class="btn btn-secondary">Batal</a>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-calculate ROP preview
            function updateROPPreview() {
                const safetyStock = parseFloat(document.getElementById('safety_stock').value) || 0;
                const leadTime = parseFloat(document.getElementById('lead_time').value) || 0;
                const avgDaily = parseFloat(document.getElementById('avg_daily').value) || 0;

                const rop = (avgDaily * leadTime) + safetyStock;

                document.getElementById('rop-preview').innerHTML =
                    `ROP = (${avgDaily} × ${leadTime}) + ${safetyStock} = <strong>${rop.toFixed(0)} unit</strong>`;
            }

            document.getElementById('safety_stock').addEventListener('input', updateROPPreview);
            document.getElementById('lead_time').addEventListener('input', updateROPPreview);
            document.getElementById('avg_daily').addEventListener('input', updateROPPreview);
        </script>
    @endpush
@endsection
