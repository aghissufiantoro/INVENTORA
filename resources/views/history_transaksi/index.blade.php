@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">📜 Riwayat Transaksi</h2>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="w-full border-collapse text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Invoice</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3 text-right">Total</th>
                    <th class="p-3 text-right">Bayar</th>
                    <th class="p-3 text-right">Kembalian</th>
                    <th class="p-3 text-center">Kasir</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksis as $t)
                    <tr class="border-t hover:bg-gray-50 transition">
                        <td class="p-3 font-semibold">{{ $t->invoice_number }}</td>
                        <td class="p-3">{{ $t->tanggal }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($t->paid_amount, 0, ',', '.') }}</td>
                        <td class="p-3 text-right">Rp {{ number_format($t->change_amount, 0, ',', '.') }}</td>
                        <td class="p-3 text-center">{{ $t->user->name ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ route('history_transaksi.show', $t->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 p-3">Belum ada transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
