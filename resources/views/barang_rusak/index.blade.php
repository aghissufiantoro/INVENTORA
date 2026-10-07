@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h3 class="mb-4">Data Barang Rusak</h3>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Form Tambah Barang Rusak --}}
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                Ajukan Barang Rusak
            </div>
            <div class="card-body">

                <form action="{{ route('barangrusak.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="produk_id" id="produkSelect" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} (Stok: {{ $p->stok }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Rusak</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="Masukkan jumlah"
                            min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <textarea name="alasan" class="form-control" placeholder="Jelaskan kerusakannya..." rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto (Opsional)</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>

                    <button class="btn btn-danger" type="submit">Kirim Pengajuan</button>
                </form>

            </div>
        </div>

        {{-- Tabel Data Barang Rusak --}}
        <div class="card">
            <div class="card-header bg-secondary text-white">
                Daftar Laporan Barang Rusak
            </div>
            <div class="card-body table-responsive">

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Alasan</th>
                            <th>Foto</th>
                            <th>Diajukan Oleh</th>
                            <th>Status</th>
                            <th width="180px">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($barangRusak as $i => $rusak)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $rusak->produk->nama }}</td>
                                <td>{{ $rusak->jumlah }}</td>
                                <td>{{ $rusak->alasan }}</td>

                                {{-- Foto --}}
                                <td>
                                    @if ($rusak->foto)
                                        <img src="{{ asset('uploads/barang_rusak/' . $rusak->foto) }}" width="70"
                                            class="rounded">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>{{ $rusak->user->name }}</td>

                                {{-- Status --}}
                                <td>
                                    @if ($rusak->status == 'menunggu')
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @elseif($rusak->status == 'disetujui')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>

                                {{-- Tombol Aksi --}}
                                <td>

                                    {{-- Approve / Reject hanya jika status menunggu --}}
                                    @if ($rusak->status == 'menunggu')
                                        @if (Auth::user()->role === 'owner')
                                            <form action="{{ route('barangrusak.approve', $rusak->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-success btn-sm">Setujui</button>
                                            </form>

                                            <form action="{{ route('barangrusak.reject', $rusak->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button class="btn btn-warning btn-sm">Tolak</button>
                                            </form>
                                        @endif
                                    @endif

                                    {{-- Hapus --}}
                                    <form action="{{ route('barangrusak.destroy', $rusak->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data barang rusak.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

    </div>
@endsection

<script>
    $(document).ready(function() {
        $('#produkSelect').select2({
            placeholder: "Cari nama produk...",
            allowClear: true
        });
    });
</script>


{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
