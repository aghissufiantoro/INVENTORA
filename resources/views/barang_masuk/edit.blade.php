@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Edit Barang Masuk</h3>
        <a href="{{ route('barang_masuk.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Alert Warning untuk data verified --}}
    @if($barangMasuk->status == 'verified')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Perhatian!</strong> Data ini sudah terverifikasi. Perubahan data akan mempengaruhi stok barang.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Alert khusus untuk karyawan --}}
    @if(auth()->user()->role == 'karyawan')
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Info:</strong> Sebagai karyawan, Anda hanya dapat mengubah barang, jumlah, dan keterangan. Harga tidak dapat diubah.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Alert Error --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Alert Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Terjadi kesalahan validasi:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('barang_masuk.update', $barangMasuk->id) }}" method="POST" id="formEditBarangMasuk">
                @csrf
                @method('PUT')

                {{-- Info Data Dibuat --}}
                <div class="alert alert-light border mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="bi bi-person"></i> <strong>Dibuat oleh:</strong> {{ $barangMasuk->user->name ?? '-' }}
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> <strong>Tanggal:</strong> {{ $barangMasuk->created_at->format('d-m-Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="product_id" class="form-label fw-semibold">
                        Nama Barang <span class="text-danger">*</span>
                    </label>
                    <select name="product_id" id="product_id" 
                            class="form-select @error('product_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Barang --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" 
                                {{ (old('product_id', $barangMasuk->product_id) == $product->id) ? 'selected' : '' }}>
                                {{ $product->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jumlah" class="form-label fw-semibold">
                        Jumlah <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="jumlah" id="jumlah" 
                           class="form-control @error('jumlah') is-invalid @enderror" 
                           value="{{ old('jumlah', $barangMasuk->jumlah) }}"
                           min="1" 
                           placeholder="Masukkan jumlah barang"
                           required>
                    @error('jumlah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Harga hanya untuk admin/owner --}}
                @if(auth()->user()->role != 'karyawan')
                    <div class="mb-3">
                        <label for="harga_beli" class="form-label fw-semibold">
                            Harga Beli Satuan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="harga_beli" id="harga_beli" 
                                   class="form-control @error('harga_beli') is-invalid @enderror" 
                                   value="{{ old('harga_beli', $barangMasuk->harga_beli) }}"
                                   min="0" 
                                   placeholder="0"
                                   required>
                        </div>
                        @error('harga_beli')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- <div class="mb-3">
                        <label for="total_harga" class="form-label fw-semibold">Total Harga Beli</label>
                        <input type="text" id="total_harga" class="form-control bg-light" 
                               value="Rp 0" readonly>
                        <small class="text-muted">Otomatis dihitung dari Jumlah × Harga Beli</small>
                    </div> --}}
                @else
                    <div class="alert alert-light border" role="alert">
                        <i class="bi bi-lock-fill me-2"></i>
                        <strong>Harga Saat Ini:</strong> 
                        @if($barangMasuk->harga_beli > 0)
                            Rp{{ number_format($barangMasuk->harga_beli, 0, ',', '.') }}
                        @else
                            <span class="badge bg-secondary">Belum diisi</span>
                        @endif
                        <br>
                        <small class="text-muted">Anda tidak dapat mengubah harga. Hubungi admin/owner untuk mengubah harga.</small>
                    </div>
                @endif

                <div class="mb-3">
                    <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" 
                              class="form-control @error('keterangan') is-invalid @enderror" 
                              placeholder="Contoh: Barang dari supplier A, kondisi baik">{{ old('keterangan', $barangMasuk->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status Badge --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <div>
                        <span class="badge bg-{{ $barangMasuk->status == 'verified' ? 'success' : 'warning' }} fs-6">
                            <i class="bi bi-{{ $barangMasuk->status == 'verified' ? 'check-circle' : 'clock' }}"></i>
                            {{ ucfirst($barangMasuk->status) }}
                        </span>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <span class="text-danger">*</span> Wajib diisi
                    </small>
                    <div>
                        <a href="{{ route('barang_masuk.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Barang Masuk
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const role = '{{ auth()->user()->role }}';
        
        // Hanya jalankan perhitungan total jika bukan karyawan
        if (role !== 'karyawan') {
            const jumlahInput = document.getElementById('jumlah');
            const hargaInput = document.getElementById('harga_beli');
            const totalHargaInput = document.getElementById('total_harga');

            function hitungTotal() {
                const jumlah = parseInt(jumlahInput.value) || 0;
                const harga = parseInt(hargaInput.value) || 0;
                const total = jumlah * harga;
                
                totalHargaInput.value = 'Rp ' + total.toLocaleString('id-ID');
            }

            // Hitung total saat load
            hitungTotal();

            jumlahInput.addEventListener('input', hitungTotal);
            hargaInput.addEventListener('input', hitungTotal);

            // Format harga input saat user mengetik
            hargaInput.addEventListener('blur', function() {
                if (this.value) {
                    const nilai = parseInt(this.value);
                    this.value = nilai;
                }
            });
        }

        // Confirm before submit
        document.getElementById('formEditBarangMasuk').addEventListener('submit', function(e) {
            const status = '{{ $barangMasuk->status }}';
            let confirmMessage = 'Apakah Anda yakin ingin mengupdate data ini?';
            
            if (status === 'verified') {
                confirmMessage = 'Data ini sudah terverifikasi. Update data akan mempengaruhi stok barang. Lanjutkan?';
            }
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });

        // Auto hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert:not(.alert-warning):not(.alert-info):not(.alert-light)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush
@endsection