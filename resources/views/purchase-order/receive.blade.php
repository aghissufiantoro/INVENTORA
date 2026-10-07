@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Penerimaan Barang - {{ $purchaseOrder->kode_po }}</h3>
            <a href="{{ route('purchase-order.index') }}" class="btn btn-secondary">← Kembali</a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Informasi Purchase Order</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Kode PO:</strong> {{ $purchaseOrder->kode_po }}</p>
                        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->tanggal)->format('d M Y') }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dibuat oleh:</strong> {{ $purchaseOrder->user->name }}</p>
                        <p><strong>Status:</strong> <span
                                class="badge bg-warning">{{ ucfirst($purchaseOrder->status) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('purchase-order.process-receive', $purchaseOrder->id) }}" method="POST">
            @csrf
            @method('POST')

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detail Barang yang Diterima</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width: 45%">Produk</th>
                                    <th style="width: 15%" class="text-center">Qty Dipesan</th>
                                    <th style="width: 20%" class="text-center">Qty Diterima</th>
                                    <th style="width: 20%" class="text-center">Stok Sekarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrder->details as $detail)
                                    <tr>
                                        <td>
                                            <strong>{{ $detail->product->nama }}</strong><br>
                                            <small class="text-muted">{{ $detail->product->kode_product }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6">{{ $detail->jumlah }}</span>
                                        </td>
                                        <td>
                                            <input type="number" name="received_qty[{{ $detail->id }}]"
                                                class="form-control text-center form-control-lg"
                                                value="{{ $detail->jumlah }}" min="0" max="{{ $detail->jumlah }}"
                                                required>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary fs-6">{{ $detail->product->stok }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <label for="notes" class="form-label">Catatan Penerimaan (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                            placeholder="Contoh: Barang diterima dalam kondisi baik, tidak ada kerusakan"></textarea>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Setelah klik "Proses Penerimaan", stok akan otomatis bertambah dan dicatat dalam laporan
                                stock movement sebagai "Purchase Order".
                            </small>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Proses Penerimaan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[name^="received_qty"]');

            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    const max = parseInt(this.getAttribute('max'));
                    const value = parseInt(this.value);

                    if (value > max) {
                        alert('Jumlah yang diterima tidak boleh melebihi jumlah yang dipesan!');
                        this.value = max;
                    }

                    if (value < 0) {
                        this.value = 0;
                    }
                });
            });
        });
    </script>
@endsection
