@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold">Daftar Barang Masuk</h3>
            <a href="{{ route('barang_masuk.create') }}" class="btn btn-primary">
                + Tambah Barang Masuk
            </a>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>Berhasil!</strong> {{ session('success') }}
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
                <strong>Terjadi kesalahan:</strong>
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
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            @if (auth()->user()->role != 'karyawan')
                                <th>Harga Beli</th>
                            @endif
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>User</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($barangMasuks as $index => $bm)
                            <tr class="text-center">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $bm->product->nama ?? '-' }}</td>
                                <td>{{ $bm->jumlah }}</td>

                                <td>
                                    @if ($bm->harga_beli > 0)
                                        <div>
                                            Rp{{ number_format($bm->harga_beli, 0, ',', '.') }}
                                        </div>
                                        @if ($bm->status === 'pending' && auth()->user()->role === 'owner')
                                            <button type="button" class="btn btn-sm btn-outline-primary mt-1"
                                                data-bs-toggle="modal" data-bs-target="#editHargaModal{{ $bm->id }}">
                                                <i class="bi bi-pencil"></i> Ubah
                                            </button>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Belum diisi</span>
                                        @if ($bm->status === 'pending' && auth()->user()->role === 'owner')
                                            <button type="button" class="btn btn-sm btn-primary mt-1"
                                                data-bs-toggle="modal" data-bs-target="#editHargaModal{{ $bm->id }}">
                                                <i class="bi bi-plus-circle"></i> Isi Harga
                                            </button>
                                        @endif
                                    @endif

                                    {{-- Modal Edit Harga --}}
                                    <div class="modal fade" id="editHargaModal{{ $bm->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('barang_masuk.update_harga', $bm->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-pencil-square"></i> Ubah Harga Beli
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Produk:</label>
                                                            <p class="form-control-plaintext">
                                                                {{ $bm->product->nama ?? '-' }}</p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Jumlah:</label>
                                                            <p class="form-control-plaintext">{{ $bm->jumlah }} unit
                                                            </p>
                                                        </div>
                                                        @if ($bm->product->harga_beli > 0)
                                                            <div class="alert alert-info">
                                                                <i class="bi bi-info-circle"></i>
                                                                <strong>Harga Beli Sebelumnya:</strong><br>
                                                                Rp{{ number_format($bm->product->harga_beli, 0, ',', '.') }}
                                                            </div>
                                                        @endif
                                                        <div class="mb-3">
                                                            <label for="harga_beli{{ $bm->id }}"
                                                                class="form-label fw-semibold">
                                                                Harga Beli Baru <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" name="harga_beli"
                                                                    id="harga_beli{{ $bm->id }}" class="form-control"
                                                                    value="{{ $bm->harga_beli ?? $bm->product->harga_beli }}"
                                                                    min="0" step="0.01" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-save"></i> Simpan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $bm->status == 'verified' ? 'success' : 'warning' }}">
                                        {{ ucfirst($bm->status) }}
                                    </span>
                                </td>
                                <td>{{ $bm->keterangan ?? '-' }}</td>
                                <td>{{ $bm->user->name ?? '-' }}</td>
                                <td>{{ $bm->created_at->format('d-m-Y') }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Tombol Edit --}}
                                        @if (auth()->user()->role == 'owner' ||
                                                auth()->user()->role == 'admin' ||
                                                (auth()->user()->role == 'karyawan' && $bm->user_id == auth()->id() && $bm->status != 'verified'))
                                            <a href="{{ route('barang_masuk.edit', $bm->id) }}"
                                                class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endif

                                        {{-- Tombol Verifikasi (hanya owner) --}}
                                        @if (auth()->user()->role == 'owner')
                                            @if ($bm->status != 'verified')
                                                @if ($bm->hasPrice())
                                                    <form action="{{ route('barang_masuk.verify', $bm->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Yakin ingin memverifikasi barang masuk ini?')"
                                                        class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-success btn-sm" type="submit"
                                                            title="Verifikasi">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary btn-sm" disabled
                                                        title="Isi harga terlebih dahulu">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill"></i> Verified
                                                </span>
                                            @endif

                                            {{-- Tombol Hapus (hanya owner) --}}
                                            <form action="{{ route('barang_masuk.destroy', $bm->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin ingin menghapus data ini?')"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-danger btn-sm" type="submit" title="Hapus">
                                                    <i class="harga bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if ($bm->status == 'verified')
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill"></i> Verified
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i> Pending
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role != 'karyawan' ? '9' : '8' }}"
                                    class="text-center text-muted py-3">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data barang masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Auto hide alerts after 5 seconds --}}
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-warning):not(.alert-info)');
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
