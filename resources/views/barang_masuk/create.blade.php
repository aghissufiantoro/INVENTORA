@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Tambah Barang Masuk</h3>
            <a href="{{ route('barang_masuk.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Alert khusus untuk karyawan --}}
        @if (auth()->user()->role == 'karyawan')
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Info:</strong> Sebagai karyawan, Anda hanya perlu mengisi barang, jumlah, dan keterangan. Harga beli
                akan mengikuti harga pembelian terakhir.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Alert Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Terjadi kesalahan validasi:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('barang_masuk.store') }}" method="POST" id="formBarangMasuk">
                    @csrf

                    <div class="mb-3">
                        <label for="product_id" class="form-label fw-semibold">
                            Nama Barang <span class="text-danger">*</span>
                        </label>
                        <select name="product_id" id="product_id"
                            class="form-select @error('product_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-harga-beli="{{ floatval($product->harga_beli) }}"
                                    {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->nama }}
                                    @if (auth()->user()->role != 'karyawan')
                                        <small class="text-muted">Harga:
                                            {{ number_format($product->harga_beli, 2) }}</small>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Info Harga Beli UNTUK SEMUA ROLE --}}
                    <div id="infoHargaBeli" class="alert alert-light border mb-3" style="display: none;">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Harga Beli Saat Ini:</strong>
                                <span id="displayHargaBeli" class="text-primary fs-5">-</span>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">
                                    <i class="bi bi-clock-history"></i> Pembelian terakhir
                                </small>
                            </div>
                        </div>
                        @if (auth()->user()->role == 'karyawan')
                            <hr class="my-2">
                            <small class="text-muted">
                                <i class="bi bi-lightbulb"></i> Harga ini akan digunakan untuk barang masuk Anda. Owner
                                dapat mengubahnya sebelum verifikasi jika ada perubahan harga.
                            </small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="jumlah" class="form-label fw-semibold">
                            Jumlah <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="jumlah" id="jumlah"
                            class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}"
                            min="1" placeholder="Masukkan jumlah barang" required>
                        @error('jumlah')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Harga hanya untuk admin/owner --}}
                    @if (auth()->user()->role != 'karyawan')
                        <div class="mb-3">
                            <label for="harga_beli" class="form-label fw-semibold">
                                Harga Beli Satuan <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="harga_beli" id="harga_beli"
                                    class="form-control @error('harga_beli') is-invalid @enderror"
                                    value="{{ old('harga_beli') }}" min="0" step="0.01" placeholder="0" required>
                            </div>
                            @error('harga_beli')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                            class="form-control @error('keterangan') is-invalid @enderror"
                            placeholder="Contoh: Barang dari supplier A, kondisi baik">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                <i class="bi bi-save"></i> Simpan Barang Masuk
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
                const productSelect = document.getElementById('product_id');
                const hargaBeliInput = document.getElementById('harga_beli');
                const infoDiv = document.getElementById('infoHargaBeli');
                const displaySpan = document.getElementById('displayHargaBeli');

                productSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const hargaBeli = parseFloat(selectedOption.getAttribute('data-harga-beli')) || 0;

                    if (this.value) {
                        infoDiv.style.display = 'block';
                        if (hargaBeli > 0) {
                            displaySpan.textContent = 'Rp ' + hargaBeli.toLocaleString('id-ID');
                            displaySpan.classList.remove('text-warning');
                            displaySpan.classList.add('text-primary');
                            if (hargaBeliInput && role !== 'karyawan') {
                                hargaBeliInput.value = hargaBeli;
                            }
                        } else {
                            displaySpan.textContent = 'Belum ada harga beli sebelumnya';
                            displaySpan.classList.remove('text-primary');
                            displaySpan.classList.add('text-warning');
                            if (hargaBeliInput && role !== 'karyawan') {
                                hargaBeliInput.value = '';
                            }
                        }
                    } else {
                        infoDiv.style.display = 'none';
                        if (hargaBeliInput) hargaBeliInput.value = '';
                    }
                });

                // Format otomatis input harga beli jadi Rp saat mengetik
                if (hargaBeliInput) {
                    hargaBeliInput.addEventListener('input', function() {
                        let value = this.value.replace(/\D/g, '');
                        this.value = value ? parseInt(value).toLocaleString('id-ID') : '';
                    });
                }
            });
        </script>
    @endpush

@endsection
