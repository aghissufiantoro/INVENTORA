@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-4">
        <div class="container mx-auto px-15">
            <!-- Header -->
            <div class="text-left mb-6">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">🛒 Kasir Invora</h1>
                <p class="text-gray-600">Kelola transaksi dengan cepat dan mudah</p>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="bg-green-500 text-white px-4 py-2 rounded-lg mb-4 text-center shadow-md">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-500 text-white px-4 py-2 rounded-lg mb-4 text-center shadow-md">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Main Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Left: Search & Products -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Search -->
                    <div class="bg-white rounded-xl shadow-lg p-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">🔍</span>
                            <input type="text" id="searchInput" placeholder="Cari produk..."
                                class="flex-1 border-0 focus:ring-0 text-lg">
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4">
                            <h3 class="text-lg font-semibold flex items-center">
                                <span class="mr-2">📦</span> Daftar Produk
                            </h3>
                        </div>
                        <div class="overflow-y-auto max-h-120">
                            <table class="w-full">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="p-3 text-left font-semibold">Produk</th>
                                        <th class="p-3 text-center font-semibold">Kategori</th>
                                        <th class="p-3 text-center font-semibold">Harga</th>
                                        <th class="p-3 text-center font-semibold">Stok</th>
                                        <th class="p-3 text-center font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="productTable">
                                    @foreach ($products as $p)
                                        <tr class="hover:bg-blue-50 transition">
                                            <td class="p-3 font-medium">{{ $p->nama }}</td>
                                            <td class="p-3 text-center">{{ $p->kategori->nama ?? '-' }}</td>
                                            <td class="p-3 text-center font-bold text-green-600">Rp
                                                {{ number_format($p->harga, 0, ',', '.') }}</td>
                                            <td class="p-3 text-center">{{ $p->stok }}</td>
                                            <td class="p-3 text-center">
                                                <button data-id="{{ $p->id }}" data-nama="{{ $p->nama }}"
                                                    data-stok="{{ $p->stok }}" data-harga="{{ $p->harga }}"
                                                    class="tambah-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-full transition transform hover:scale-105">
                                                    ➕ Tambah
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right: Add to Cart & Cart/Payment -->
                <div class="space-y-4">
                    <!-- Add to Cart Form -->
                    <div id="formTransaksi" class="bg-white rounded-xl shadow-lg p-4">
                        <h3 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                            <span class="mr-2">🧾</span> Tambah ke Keranjang
                        </h3>
                        <form method="POST" action="{{ route('pos.add') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" id="product_id" name="product_id">
                            <div>
                                <label class="block text-sm font-medium mb-1">Produk</label>
                                <input type="text" id="nama_produk" class="w-full border rounded px-3 py-2 bg-gray-50"
                                    readonly>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Qty</label>
                                    <input type="number" name="quantity" id="jumlah"
                                        class="w-full border rounded px-3 py-2" required min="1">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Harga</label>
                                    <input type="text" id="harga" class="w-full border rounded px-3 py-2 bg-gray-50"
                                        readonly>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit"
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded transition">
                                    ✅ Tambah
                                </button>
                                <button type="button" id="cancelBtn"
                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 rounded transition">
                                    ❌ Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Cart & Payment -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white p-4">
                            <h3 class="text-lg font-semibold flex items-center">
                                <span class="mr-2">🛒</span> Keranjang & Bayar
                            </h3>
                        </div>
                        <div class="p-4 overflow-y-auto max-h-80">
                            <!-- Cart Items -->
                            <div class="space-y-2 mb-4">
                                @forelse ($cart as $item)
                                    <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                        <div>
                                            <p class="font-medium">{{ $item['nama'] }}</p>
                                            <p class="text-sm text-gray-600">Qty: {{ $item['quantity'] }} x Rp
                                                {{ number_format($item['harga'], 0, ',', '.') }}</p>
                                        </div>
                                        <p class="font-bold text-green-600">Rp
                                            {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 py-4">
                                        <span class="text-3xl">🛒</span>
                                        <p>Keranjang kosong</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Total -->
                            <div class="bg-gray-100 p-3 rounded mb-4">
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total:</span>
                                    <span class="text-green-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <form action="{{ route('pos.checkout') }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium mb-1">Bayar (Rp)</label>
                                    <input type="number" id="paid_amount" name="paid_amount" value="{{ $totalAmount }}"
                                        min="{{ $totalAmount }}" required class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kembalian (Rp)</label>
                                    <input type="text" id="change_amount" name="change_amount" value="0" readonly
                                        class="w-full border rounded px-3 py-2 bg-gray-50">
                                </div>
                                <button type="submit"
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded transition @if (empty($cart)) opacity-50 cursor-not-allowed @endif">
                                    🎉 Selesai
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        // Live Search
        const searchInput = document.getElementById('searchInput');
        const productTable = document.getElementById('productTable').rows;

        searchInput.addEventListener('keyup', () => {
            const filter = searchInput.value.toLowerCase();
            Array.from(productTable).forEach(row => {
                const nama = row.cells[0].innerText.toLowerCase();
                row.style.display = nama.includes(filter) ? '' : 'none';
            });
        });

        // Add to Cart
        document.querySelectorAll('.tambah-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = document.getElementById('formTransaksi');


                document.getElementById('product_id').value = this.dataset.id;
                document.getElementById('nama_produk').value = this.dataset.nama;
                document.getElementById('harga').value = 'Rp ' + Number(this.dataset.harga).toLocaleString(
                    'id-ID');
                document.getElementById('jumlah').max = this.dataset.stok;
            });
        });

        // Cancel
        document.getElementById('cancelBtn').addEventListener('click', () => {
            document.getElementById('product_id').value = 0;
            document.getElementById('nama_produk').value = '';
            document.getElementById('harga').value = 'Rp 0';
            document.getElementById('jumlah').value = 0;
        });

        // Calculate Change
        function calculateChange(total) {
            const paidInput = document.getElementById('paid_amount');
            const changeInput = document.getElementById('change_amount');

            const paid = parseFloat(paidInput.value) || 0;
            const change = paid - total;

            changeInput.value = 'Rp ' + change.toLocaleString('id-ID', {
                minimumFractionDigits: 2
            });
            paidInput.setCustomValidity(paid < total ? 'Jumlah bayar tidak cukup.' : '');
        }

        window.onload = () => calculateChange({{ $totalAmount }});
    </script>
@endsection
