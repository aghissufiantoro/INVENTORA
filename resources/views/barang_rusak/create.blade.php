@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3">➕ Tambah Barang Rusak</h4>

    <form action="{{ route('retur.store') }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        <div class="mb-3">
            <label class="form-label">Pilih Produk</label>
            <select name="product_id" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->nama }} (Stok: {{ $product->stok }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Jumlah Retur</label>
            <input type="number" name="jumlah" class="form-control" min="1" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Alasan Retur</label>
            <textarea name="alasan" class="form-control" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-100">Kirim Retur</button>
    </form>
</div>
@endsection
