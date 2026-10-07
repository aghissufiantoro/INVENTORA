@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 mt-1">Selamat datang, {{ Auth::user()->name }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ now()->format('d F Y') }}</p>
            <p class="text-sm text-gray-400">{{ now()->format('H:i') }} WIB</p>
        </div>
    </div>

    @if (Auth::user()->role === 'owner')
        {{-- === OWNER DASHBOARD === --}}

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Penjualan Hari Ini --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Penjualan Hari Ini</p>
                        <p class="text-3xl font-bold mt-2">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                        <div class="flex items-center mt-3">
                            <span class="text-green-100 text-sm">
                                @if($persentasePenjualan >= 0)
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                                {{ abs($persentasePenjualan) }}%
                            </span>
                            <span class="text-green-100 text-xs ml-2">vs kemarin</span>
                        </div>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Jumlah Transaksi --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Transaksi Hari Ini</p>
                        <p class="text-3xl font-bold mt-2">{{ $jumlahTransaksi }}</p>
                        <p class="text-blue-100 text-xs mt-3">Rata-rata: {{ number_format($rataTransaksiMingguan, 1) }}/hari</p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Barang Terjual --}}
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Barang Terjual</p>
                        <p class="text-3xl font-bold mt-2">{{ $totalBarangTerjual }}</p>
                        <div class="flex items-center mt-3">
                            <span class="text-orange-100 text-sm">
                                @if($persentaseBarangTerjual >= 0)
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                                {{ abs($persentaseBarangTerjual) }}%
                            </span>
                            <span class="text-orange-100 text-xs ml-2">vs kemarin</span>
                        </div>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Stok Kritis --}}
            <div class="bg-gradient-to-br from-red-500 to-red-600 p-6 rounded-xl shadow-lg text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Stok Kritis</p>
                        <p class="text-3xl font-bold mt-2">{{ $stokKritis }}</p>
                        <p class="text-red-100 text-xs mt-3">Perlu segera restock</p>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Tren Penjualan --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Tren Penjualan 7 Hari</h3>
                    <span class="text-sm text-gray-500">Last 7 days</span>
                </div>
                <div style="height: 250px;">
                    <canvas id="chartTrenPenjualan"></canvas>
                </div>
            </div>

            {{-- Top 5 Produk Terlaris --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Top 5 Produk Terlaris</h3>
                    <span class="text-sm text-gray-500">All time</span>
                </div>
                <div style="height: 250px;">
                    <canvas id="chartTopProduk"></canvas>
                </div>
            </div>
        </div>

        {{-- Status Stok & Detail Stok Kritis --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Status Stok --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Status Stok</h3>
                <div style="height: 200px;">
                    <canvas id="chartStatusStok"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Aman</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $dataStatusStok[0] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Rendah</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $dataStatusStok[1] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600">Habis</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $dataStatusStok[2] }}</span>
                    </div>
                </div>
            </div>

            {{-- Detail Stok Kritis --}}
            <div class="bg-white p-6 rounded-xl shadow-lg lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Detail Stok Kritis</h3>
                    <a href="{{ route('product.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Produk</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700">Stok</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700">ROP</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-gray-700">Kurang</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailStokKritis as $item)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-gray-800">{{ $item->nama }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">
                                            {{ $item->stok }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-600">{{ $item->rop }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="text-red-600 font-semibold">{{ $item->reorder_qty }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('barang-masuk.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                            Restock &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p>Semua stok dalam kondisi aman</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @else
        {{-- === KARYAWAN DASHBOARD === --}}

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                <p class="text-blue-100 text-sm font-medium">Transaksi Hari Ini</p>
                <p class="text-3xl font-bold mt-2">{{ $jumlahTransaksi }}</p>
                <p class="text-blue-100 text-xs mt-3">Transaksi berhasil</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                <p class="text-green-100 text-sm font-medium">Penjualan Hari Ini</p>
                <p class="text-3xl font-bold mt-2">Rp {{ number_format($penjualanHariIni, 0, ',', '.') }}</p>
                <p class="text-green-100 text-xs mt-3">Total penjualan</p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white">
                <p class="text-purple-100 text-sm font-medium">Barang Terjual</p>
                <p class="text-3xl font-bold mt-2">{{ $totalBarangTerjual }}</p>
                <p class="text-purple-100 text-xs mt-3">Item terjual</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('pos.index') }}" class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <svg class="w-12 h-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Kasir</span>
                </a>

                <a href="{{ route('barang-masuk.index') }}" class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                    <svg class="w-12 h-12 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Barang Masuk</span>
                </a>

                <a href="{{ route('barang-rusak.index') }}" class="flex flex-col items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                    <svg class="w-12 h-12 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Barang Rusak</span>
                </a>

                <a href="{{ route('retur.index') }}" class="flex flex-col items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors">
                    <svg class="w-12 h-12 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Retur</span>
                </a>
            </div>
        </div>

        {{-- Stok Kritis Alert --}}
        @if($stokKritis > 0)
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-xl shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-red-800">Perhatian: {{ $stokKritis }} Produk Stok Kritis</h3>
                        <p class="text-red-700 text-sm mt-1">Segera laporkan ke owner untuk restock</p>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    @if(Auth::user()->role === 'owner')
        // Tren Penjualan Chart
        const ctxTren = document.getElementById('chartTrenPenjualan').getContext('2d');
        new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: @json($labelsTrenPenjualan),
                datasets: [{
                    label: 'Penjualan',
                    data: @json($dataTrenPenjualan),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Top Produk Chart
        const ctxTop = document.getElementById('chartTopProduk').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: @json($labelsTopProduk),
                datasets: [{
                    label: 'Terjual',
                    data: @json($dataTopProduk),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Status Stok Chart
        const ctxStatus = document.getElementById('chartStatusStok').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Rendah', 'Habis'],
                datasets: [{
                    data: @json($dataStatusStok),
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    @endif
</script>
@endsection
