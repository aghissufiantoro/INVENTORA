@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">🧾 Detail Transaksi</h2>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <p><strong>Invoice:</strong> {{ $transaksi->invoice_number }}</p>
        <p><strong>Tanggal:</strong> {{ $transaksi->tanggal }}</p>
        <p><strong>Kasir:</strong> {{ $transaksi->user->name ?? '-' }}</p>
        <p><strong>Total:</strong> Rp {{ number_format($transaksi->total_amount, 0, ',', '.') }}</p>
        <p><strong>Bayar:</strong> Rp {{ number_format($transaksi->paid_amount, 0, ',', '.') }}</p>
        <p><strong>Kembalian:</strong> Rp {{ number_format($transaksi->change_amount, 0, ',', '.') }}</p>
    </div>

    <h3 class="text-xl font-semibold mb-3">Daftar Barang</h3>
    <table class="w-full border text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nama Produk</th>
                <th class="p-2 text-center">Jumlah</th>
                <th class="p-2 text-right">Harga</th>
                <th class="p-2 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi->details as $item)
                <tr class="border-t">
                    <td class="p-2">{{ $item->product->nama }}</td>
                    <td class="p-2 text-center">{{ $item->jumlah }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="p-2 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        <a href="{{ route('history_transaksi.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">← Kembali</a>
    </div>
</div>
@endsection
