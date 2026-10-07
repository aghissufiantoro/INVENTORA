@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Buat Purchase Order Baru</h3>

    <form action="{{ route('purchase-order.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                Barang Rekomendasi (Stok < ROP)
            </div>
            <div class="card-body">
                @if($recommendedProducts->isEmpty())
                    <p>Tidak ada barang yang perlu direstok.</p>
                @else
                    @foreach($recommendedProducts as $product)
                        <div class="mb-3 border p-3 rounded bg-light">
                            <label>{{ $product->nama }} (Stok: {{ $product->stok }}, ROP: {{ $product->rop }})</label>
                            <input type="hidden" name="product_id[]" value="{{ $product->id }}">
                            <input type="number" name="jumlah[]" class="form-control mt-2" placeholder="Masukkan jumlah">
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                Tambah Barang Lain
            </div>
            <div class="card-body">
                <div id="extra-products"></div>
                <button type="button" class="btn btn-outline-primary" id="addProduct">+ Tambah Barang</button>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Simpan Purchase Order</button>
        <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">Batal</a>
    </form>
</div>

<script>
document.getElementById('addProduct').addEventListener('click', function() {
    const container = document.getElementById('extra-products');
    const html = `
        <div class="row mb-3 align-items-center border-bottom pb-2">
            <div class="col-md-6">
                <select name="product_id[]" class="form-select">
                    <option value="">Pilih Barang</option>
                    @foreach($allProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-item">Hapus</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);

    container.querySelectorAll('.remove-item').forEach(btn => {
        btn.onclick = () => btn.closest('.row').remove();
    });
});
</script>
@endsection
