@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Laporan Stok</h2>
        <div class="flex gap-2">
            <a href="{{ route('laporan_stok.export-pdf', request()->all()) }}"
                class="bg-red-500 text-white px-3 py-1 rounded">Export PDF</a>
            <a href="{{ route('laporan_stok.export-excel', request()->all()) }}"
                class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                Export Excel
            </a>

        </div>
    </div>

    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="search" placeholder="Cari barang..." value="{{ request('search') }}"
            class="border rounded px-3 py-1">
        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Cari</button>
    </form>

    <table class="w-full border">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-3 py-1">No</th>
                <th class="border px-3 py-1">Tanggal</th>
                <th class="border px-3 py-1">Nama Barang</th>
                <th class="border px-3 py-1">Tipe</th>
                <th class="border px-3 py-1">Transaksi</th>
                <th class="border px-3 py-1">Jumlah</th>
                <th class="border px-3 py-1">Harga</th>
                <th class="border px-3 py-1">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $i => $item)
                <tr>
                    <td class="border px-3 py-1">{{ $i + 1 }}</td>
                    <td class="border px-3 py-1">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td class="border px-3 py-1">{{ $item->product->nama ?? '-' }}</td>
                    <td class="border px-3 py-1">
                        @if ($item->type === 'in')
                            <span class="text-green-600 font-semibold">Masuk</span>
                        @else
                            <span class="text-red-600 font-semibold">Keluar</span>
                        @endif
                    </td>
                    <td class="border px-3 py-1">{{ ucfirst($item->transaction_type ?? '-') }}</td>
                    <td class="border px-3 py-1">{{ $item->quantity }}</td>
                    <td class="border px-3 py-1">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                    <td class="border px-3 py-1">Rp
                        {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border px-3 py-2 text-center text-gray-500">Tidak ada data pergerakan stok
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
