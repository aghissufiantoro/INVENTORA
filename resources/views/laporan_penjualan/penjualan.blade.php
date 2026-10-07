@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Laporan Penjualan</h2>
        <div class="flex gap-2">
            <a href="{{ route('laporan_penjualan.penjualan.pdf', request()->all()) }}"
                class="bg-red-500 text-white px-3 py-1 rounded">Export PDF</a>
            <a href="{{ route('laporan_penjualan.penjualan.excel', request()->all()) }}"
                class="bg-green-500 text-white px-3 py-1 rounded">Export Excel</a>
        </div>


    </div>

    {{-- FILTER DAN SORT --}}
    <form method="GET" class="mb-4 grid md:grid-cols-5 sm:grid-cols-1 gap-2">
        <input type="date" name="tanggal_awal" value="{{ request('tanggal_awal') }}" class="border rounded px-3 py-1">
        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="border rounded px-3 py-1">

        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice / kasir..."
            class="border rounded px-3 py-1">

        <select name="sort" class="border rounded px-3 py-1">
            <option value="">Urutkan</option>
            <option value="tanggal_asc" {{ request('sort') == 'tanggal_asc' ? 'selected' : '' }}>Tanggal Terlama</option>
            <option value="total_desc" {{ request('sort') == 'total_desc' ? 'selected' : '' }}>Total Tertinggi</option>
            <option value="total_asc" {{ request('sort') == 'total_asc' ? 'selected' : '' }}>Total Terendah</option>
        </select>

        <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Terapkan</button>

        @if (request()->query())
            <button type="button" id="resetFilterBtn" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">
                Reset
            </button>
        @endif
    </form>

    {{-- RINGKASAN --}}
    <div class="bg-gray-100 p-3 rounded-lg mb-4 flex justify-between items-center">
        <p class="text-gray-700 font-medium">Total Transaksi: <span class="font-bold">{{ $totalTransaksi }}</span></p>
    </div>

    {{-- TABEL --}}
    <table class="w-full border text-sm">
        <thead class="bg-gray-200">
            <tr>
                <th class="border px-3 py-1">No</th>
                <th class="border px-3 py-1">Invoice</th>
                <th class="border px-3 py-1">Tanggal</th>
                <th class="border px-3 py-1">Kasir</th>
                <th class="border px-3 py-1">Total</th>
                <th class="border px-3 py-1">Dibayar</th>
                <th class="border px-3 py-1">Kembalian</th>
                <th class="border px-3 py-1">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualan as $i => $pos)
                <tr class="hover:bg-gray-50">
                    <td class="border px-3 py-1 text-center">{{ $i + 1 }}</td>
                    <td class="border px-3 py-1">{{ $pos->invoice_number }}</td>
                    <td class="border px-3 py-1">{{ $pos->tanggal }}</td>
                    <td class="border px-3 py-1">{{ $pos->user->name ?? '-' }}</td>
                    <td class="border px-3 py-1">Rp {{ number_format($pos->total_amount, 0, ',', '.') }}</td>
                    <td class="border px-3 py-1">Rp {{ number_format($pos->paid_amount, 0, ',', '.') }}</td>
                    <td class="border px-3 py-1">Rp {{ number_format($pos->change_amount, 0, ',', '.') }}</td>
                    <td class="border px-3 py-1 text-center">
                        <button onclick="toggleDetail({{ $pos->id }})"
                            class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Lihat Detail</button>
                    </td>
                </tr>

                {{-- DETAIL --}}
                <tr id="detail-{{ $pos->id }}" class="hidden bg-gray-50">
                    <td colspan="8" class="border px-3 py-2">
                        <div>
                            <table class="w-full border text-xs">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-2 py-1">Produk</th>
                                        <th class="border px-2 py-1">Jumlah</th>
                                        <th class="border px-2 py-1">Harga</th>
                                        <th class="border px-2 py-1">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pos->details as $detail)
                                        <tr>
                                            <td class="border px-2 py-1">{{ $detail->product->nama ?? '-' }}</td>
                                            <td class="border px-2 py-1 text-center">{{ $detail->jumlah }}</td>
                                            <td class="border px-2 py-1 text-right">Rp
                                                {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                            <td class="border px-2 py-1 text-right">Rp
                                                {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">Tidak ada data penjualan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        function toggleDetail(id) {
            const row = document.getElementById('detail-' + id);
            row.classList.toggle('hidden');
        }

        document.getElementById('resetFilterBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            form.querySelectorAll('input, select').forEach(el => el.value = '');
            form.submit();
        });
    </script>
@endsection
